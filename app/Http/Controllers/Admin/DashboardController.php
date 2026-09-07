<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Mentor;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        $period = in_array($request->period, ['all', 'day', 'week', 'month', 'year'], true) ? $request->period : 'all';
        $from = match ($period) {
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => null,
        };
        $periodLabel = match ($period) {
            'day' => '24 giờ qua',
            'week' => '7 ngày qua',
            'month' => '1 tháng qua',
            'year' => '1 năm qua',
            default => 'Toàn thời gian',
        };
        $validStatuses = [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED];

        $actualRevenue = (float) Order::where('status', Order::STATUS_PAID)
            ->when($from, fn ($query) => $query->where('paid_at', '>=', $from))
            ->sum('total');
        [$revenueLabels, $revenueSeries, $revenueChartLabel] = $this->revenueChart($period);

        $reviewQuery = CourseReview::visible()->when($from, fn ($query) => $query->where('created_at', '>=', $from));
        $stats = [
            'users' => User::where('role', 'user')->when($from, fn ($query) => $query->where('created_at', '>=', $from))->count(),
            'courses' => Course::when($from, fn ($query) => $query->where('created_at', '>=', $from))->count(),
            'enrollments' => Enrollment::whereIn('status', $validStatuses)->when($from, fn ($query) => $query->where('enrolled_at', '>=', $from))->count(),
            'active_enrollments' => Enrollment::whereIn('status', $validStatuses)->when($from, fn ($query) => $query->where('enrolled_at', '>=', $from))->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
            'expired_enrollments' => Enrollment::whereIn('status', $validStatuses)->when($from, fn ($query) => $query->where('enrolled_at', '>=', $from))->whereNotNull('expires_at')->where('expires_at', '<=', now())->count(),
            'mentors' => Mentor::when($from, fn ($query) => $query->where('created_at', '>=', $from))->count(),
            'reviews' => (clone $reviewQuery)->count(),
            'average_rating' => (float) ((clone $reviewQuery)->avg('rating') ?? 0),
        ];

        $latestEnrollments = Enrollment::with(['user:id,name,email', 'course:id,name,price'])
            ->whereIn('status', $validStatuses)->when($from, fn ($query) => $query->where('enrolled_at', '>=', $from))->latest('enrolled_at')->limit(10)->get();
        $latestReviews = CourseReview::visible()->with(['user:id,name', 'course:id,name'])->when($from, fn ($query) => $query->where('created_at', '>=', $from))->latest()->limit(8)->get();
        $recentUsers = User::where('role', 'user')
            ->when($from, fn ($query) => $query->where('created_at', '>=', $from))
            ->withCount(['enrollments as courses_count' => fn ($query) => $query->whereIn('status', $validStatuses)->when($from, fn ($query) => $query->where('enrolled_at', '>=', $from))])
            ->latest()->limit(6)->get();
        $topCourses = Course::withCount(['enrollments as students_count' => fn ($query) => $query->whereIn('status', $validStatuses)->when($from, fn ($query) => $query->where('enrolled_at', '>=', $from))])
            ->orderByDesc('students_count')->orderBy('name')->limit(6)->get();

        return view('Admin.Dashboard', compact('stats', 'latestEnrollments', 'latestReviews', 'recentUsers', 'topCourses', 'period', 'periodLabel', 'actualRevenue', 'revenueLabels', 'revenueSeries', 'revenueChartLabel'));
    }

    private function revenueChart(string $period): array
    {
        $now = now();
        $buckets = [];

        if ($period === 'day') {
            $start = $now->copy()->subHours(24);
            for ($i = 0; $i < 8; $i++) {
                $bucketStart = $start->copy()->addHours($i * 3);
                $buckets[] = [$bucketStart, $bucketStart->copy()->addHours(3), $bucketStart->format('H:i')];
            }
            $label = 'Doanh thu theo mỗi 3 giờ';
        } elseif ($period === 'week') {
            $start = $now->copy()->subDays(6)->startOfDay();
            for ($i = 0; $i < 7; $i++) {
                $bucketStart = $start->copy()->addDays($i);
                $buckets[] = [$bucketStart, $bucketStart->copy()->addDay(), $bucketStart->format('d/m')];
            }
            $label = 'Doanh thu theo ngày';
        } elseif ($period === 'month') {
            $start = $now->copy()->subDays(29)->startOfDay();
            for ($i = 0; $i < 10; $i++) {
                $bucketStart = $start->copy()->addDays($i * 3);
                $buckets[] = [$bucketStart, $bucketStart->copy()->addDays(3), $bucketStart->format('d/m')];
            }
            $label = 'Doanh thu theo mỗi 3 ngày';
        } else {
            $start = $now->copy()->subMonths(11)->startOfMonth();
            for ($i = 0; $i < 12; $i++) {
                $bucketStart = $start->copy()->addMonths($i);
                $buckets[] = [$bucketStart, $bucketStart->copy()->addMonth(), $bucketStart->format('m/Y')];
            }
            $label = $period === 'year' ? 'Doanh thu theo tháng' : 'Xu hướng 12 tháng gần nhất';
        }

        $orders = Order::where('status', Order::STATUS_PAID)
            ->where('paid_at', '>=', $buckets[0][0])
            ->get();

        $series = collect($buckets)->map(function ($bucket) use ($orders) {
            return (float) $orders
                ->filter(fn ($order) => $order->paid_at->gte($bucket[0]) && $order->paid_at->lt($bucket[1]))
                ->sum(fn ($order) => (float) $order->total);
        })->all();

        return [collect($buckets)->pluck(2)->all(), $series, $label];
    }

}
