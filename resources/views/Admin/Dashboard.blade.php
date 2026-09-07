@extends('Admin.Layout.Index')

@section('content')
<style>
    .dashboard-container { padding: 20px 20px 0 !important; }
    .dashboard-stat-card { height: calc(100% - 20px); border-left: 4px solid #2962ff; }
    .dashboard-stat-card.success { border-left-color: #36bea6; }.dashboard-stat-card.warning { border-left-color: #ffbc34; }
    .dashboard-stat-card.danger { border-left-color: #f62d51; }.dashboard-stat-card.info { border-left-color: #4fc3f7; }
    .dashboard-stat-label { margin-bottom: 7px; color: #6c757d; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
    .dashboard-stat-value { margin: 0; color: #2c3e50; font-size: 29px; font-weight: 700; }
    .dashboard-stat-note,.dashboard-table .sub-text { color: #8a929a; font-size: 11px; }
    .dashboard-section-title { margin: 0; font-size: 17px; font-weight: 700; }
    .dashboard-table td { vertical-align: middle; }.dashboard-table .main-text { display: block; color: #3e5569; font-weight: 600; }
    .rating-stars { color: #ffbc34; white-space: nowrap; letter-spacing: 1px; }.rating-stars .empty { color: #d9dee3; }
    .review-content { max-width: 330px; white-space: normal; line-height: 1.45; }
    .rank-number { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; color: #fff; background: #2962ff; font-size: 12px; font-weight: 700; }
    .empty-dashboard { padding: 28px !important; color: #8a929a; text-align: center; }
    .dashboard-period-bar { margin-bottom: 20px; padding: 15px 18px; display: flex; align-items: center; justify-content: space-between; gap: 15px; flex-wrap: wrap; background: #fff; border: 1px solid #e9ecef; }
    .dashboard-period-title { display: grid; gap: 2px; }.dashboard-period-title strong { color: #3e5569; }.dashboard-period-title small { color: #8a929a; }
    .dashboard-period-options { display: flex; gap: 7px; flex-wrap: wrap; }.dashboard-period-options a { padding: 7px 12px; border: 1px solid #d7dde3; border-radius: 3px; color: #5c6b77; font-size: 12px; font-weight: 600; }.dashboard-period-options a:hover,.dashboard-period-options a.active { color: #fff; border-color: #2962ff; background: #2962ff; }
    .revenue-chart { height: 300px; }.revenue-chart .ct-series-a .ct-bar { stroke: #2962ff; stroke-width: 22px; }.revenue-chart .ct-grid { stroke: #edf0f2; stroke-dasharray: 2px; }.revenue-chart .ct-label { color: #7b8790; fill: #7b8790; font-size: 11px; }
</style>

<div class="page-breadcrumb">
    <div class="row"><div class="col-6 align-self-center"><h4 class="page-title">Tổng quan</h4></div><div class="col-6 align-self-center text-right"><span class="text-muted">KhoaHocPlus Admin</span></div></div>
</div>

<div class="container-fluid dashboard-container">
    <div class="dashboard-period-bar">
        <div class="dashboard-period-title"><strong>Khoảng thời gian thống kê</strong><small>Đang xem: {{ $periodLabel }}</small></div>
        <nav class="dashboard-period-options" aria-label="Chọn khoảng thời gian">
            <a href="{{ route('Admin.Dashboard', ['period' => 'all']) }}" class="{{ $period === 'all' ? 'active' : '' }}">Toàn thời gian</a>
            <a href="{{ route('Admin.Dashboard', ['period' => 'day']) }}" class="{{ $period === 'day' ? 'active' : '' }}">24 giờ qua</a>
            <a href="{{ route('Admin.Dashboard', ['period' => 'week']) }}" class="{{ $period === 'week' ? 'active' : '' }}">7 ngày qua</a>
            <a href="{{ route('Admin.Dashboard', ['period' => 'month']) }}" class="{{ $period === 'month' ? 'active' : '' }}">1 tháng qua</a>
            <a href="{{ route('Admin.Dashboard', ['period' => 'year']) }}" class="{{ $period === 'year' ? 'active' : '' }}">1 năm qua</a>
        </nav>
    </div>
    <div class="row">
        @php
            $statCards = [
                ['Tổng người dùng', $stats['users'], 'Tài khoản học viên', ''],
                ['Tổng khóa học', $stats['courses'], 'Khóa học trong hệ thống', 'info'],
                ['Tổng lượt đăng ký', $stats['enrollments'], 'Không tính lượt đã hủy', 'success'],
                ['Khóa học còn hạn', $stats['active_enrollments'], 'Quyền học đang hoạt động', 'success'],
                ['Khóa học hết hạn', $stats['expired_enrollments'], 'Cần gia hạn để tiếp tục', 'danger'],
                ['Mentor', $stats['mentors'], 'Người hướng dẫn', 'warning'],
                ['Đánh giá', $stats['reviews'], 'Tổng lượt đánh giá', 'warning'],
            ];
        @endphp
        @foreach ($statCards as [$label, $value, $note, $tone])
            <div class="col-xl-3 col-md-6"><div class="card dashboard-stat-card {{ $tone }}"><div class="card-body"><div class="dashboard-stat-label">{{ $label }}</div><h2 class="dashboard-stat-value">{{ number_format($value) }}</h2><span class="dashboard-stat-note">{{ $note }}</span></div></div></div>
        @endforeach
        <div class="col-xl-3 col-md-6"><div class="card dashboard-stat-card warning"><div class="card-body"><div class="dashboard-stat-label">Rating trung bình</div><h2 class="dashboard-stat-value">{{ $stats['reviews'] ? number_format($stats['average_rating'], 1) : '—' }} <small>★</small></h2><span class="dashboard-stat-note">Trên thang điểm 5</span></div></div></div>
        <div class="col-xl-3 col-md-6"><div class="card dashboard-stat-card success"><div class="card-body"><div class="dashboard-stat-label">Doanh thu thực tế</div><h2 class="dashboard-stat-value">{{ number_format($actualRevenue, 0, ',', '.') }} ₫</h2><span class="dashboard-stat-note">Chỉ tính đơn đã thanh toán</span></div></div></div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between flex-wrap mb-3">
                <div><h4 class="dashboard-section-title">Biểu đồ doanh thu</h4><small class="text-muted">{{ $revenueChartLabel }}</small></div>
                <strong>{{ number_format($actualRevenue, 0, ',', '.') }} ₫ · {{ $periodLabel }}</strong>
            </div>
            <div id="revenue-chart" class="ct-chart revenue-chart"></div>
            <small class="text-muted">Doanh thu chỉ tính từ đơn hàng có trạng thái đã thanh toán và dùng số tiền được lưu cố định tại thời điểm mua.</small>
        </div>
    </div>

    <div class="card">
        <div class="card-body"><h4 class="dashboard-section-title">Đăng ký khóa học gần nhất</h4></div>
        <div class="table-responsive"><table class="table table-hover dashboard-table mb-0">
            <thead><tr><th>Học viên</th><th>Khóa học</th><th>Giá hiện tại</th><th>Thời gian đăng ký</th><th>Thời hạn</th><th>Trạng thái</th></tr></thead>
            <tbody>
            @forelse ($latestEnrollments as $enrollment)
                <tr>
                    <td><span class="main-text">{{ $enrollment->user?->name ?? 'Tài khoản đã xóa' }}</span><span class="sub-text">{{ $enrollment->user?->email }}</span></td>
                    <td>@if ($enrollment->course)<a class="main-text" href="{{ route('Admin.Course.Detail', $enrollment->course) }}">{{ $enrollment->course->name }}</a>@else<span class="text-muted">Khóa học đã xóa</span>@endif</td>
                    <td><strong>{{ number_format($enrollment->course?->price ?? 0, 0, ',', '.') }} ₫</strong></td>
                    <td><span class="main-text">{{ $enrollment->enrolled_at?->format('d/m/Y') }}</span><span class="sub-text">{{ $enrollment->enrolled_at?->format('H:i') }}</span></td>
                    <td>{{ $enrollment->expires_at?->format('d/m/Y') ?? 'Không giới hạn' }}</td>
                    <td><span class="badge {{ $enrollment->isExpired() ? 'badge-danger' : 'badge-success' }}">{{ $enrollment->isExpired() ? 'Hết hạn' : 'Còn hạn' }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-dashboard">Chưa có lượt đăng ký khóa học.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="card-body pt-2"><small class="text-muted">Đây là bảng enrollment. Doanh thu thực tế phía trên được tính riêng từ các đơn hàng đã thanh toán.</small></div>
    </div>

    <div class="row">
        <div class="col-xl-7"><div class="card">
            <div class="card-body"><h4 class="dashboard-section-title">Đánh giá mới nhất</h4></div>
            <div class="table-responsive"><table class="table table-hover dashboard-table mb-0">
                <thead><tr><th>User</th><th>Khóa học</th><th>Rating</th><th>Nội dung đánh giá</th><th>Ngày</th></tr></thead>
                <tbody>
                @forelse ($latestReviews as $review)
                    <tr>
                        <td><span class="main-text">{{ $review->user?->name ?? 'Tài khoản đã xóa' }}</span></td>
                        <td>@if ($review->course)<a href="{{ route('Admin.Course.Detail', $review->course) }}">{{ $review->course->name }}</a>@else<span class="text-muted">Đã xóa</span>@endif</td>
                        <td><span class="rating-stars">{{ str_repeat('★', $review->rating) }}<span class="empty">{{ str_repeat('★', 5 - $review->rating) }}</span></span></td>
                        <td class="review-content">{{ Illuminate\Support\Str::limit($review->comment ?: 'Không có nhận xét', 90) }}</td>
                        <td class="text-nowrap">{{ $review->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-dashboard">Chưa có đánh giá nào.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div></div>

        <div class="col-xl-5"><div class="card">
            <div class="card-body"><h4 class="dashboard-section-title">Khóa học nhiều học viên nhất</h4></div>
            <div class="table-responsive"><table class="table table-hover dashboard-table mb-0">
                <thead><tr><th>#</th><th>Khóa học</th><th>Học viên</th></tr></thead>
                <tbody>
                @forelse ($topCourses as $courseItem)
                    <tr><td><span class="rank-number">{{ $loop->iteration }}</span></td><td><a class="main-text" href="{{ route('Admin.Course.Detail', $courseItem) }}">{{ $courseItem->name }}</a><span class="sub-text">{{ number_format($courseItem->price, 0, ',', '.') }} ₫</span></td><td><strong>{{ $courseItem->students_count }}</strong></td></tr>
                @empty
                    <tr><td colspan="3" class="empty-dashboard">Chưa có khóa học.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div></div>
    </div>

    <div class="card">
        <div class="card-body"><h4 class="dashboard-section-title">Người dùng đăng ký gần đây</h4></div>
        <div class="table-responsive"><table class="table table-hover dashboard-table mb-0">
            <thead><tr><th>Họ tên</th><th>Email</th><th>Số điện thoại</th><th>Số khóa học</th><th>Trạng thái</th><th>Ngày tham gia</th></tr></thead>
            <tbody>
            @forelse ($recentUsers as $recentUser)
                <tr><td><span class="main-text">{{ $recentUser->name }}</span></td><td>{{ $recentUser->email }}</td><td>{{ $recentUser->phone ?: '—' }}</td><td><strong>{{ $recentUser->courses_count }}</strong></td><td><span class="badge {{ $recentUser->status === 'active' ? 'badge-success' : 'badge-danger' }}">{{ $recentUser->status === 'active' ? 'Hoạt động' : ucfirst($recentUser->status) }}</span></td><td>{{ $recentUser->created_at->format('d/m/Y H:i') }}</td></tr>
            @empty
                <tr><td colspan="6" class="empty-dashboard">Chưa có người dùng.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chartist === 'undefined') return;

        new Chartist.Bar('#revenue-chart', {
            labels: @json($revenueLabels),
            series: [@json($revenueSeries)]
        }, {
            fullWidth: true,
            low: 0,
            chartPadding: { top: 15, right: 15, bottom: 5, left: 10 },
            axisY: {
                onlyInteger: true,
                labelInterpolationFnc: function (value) {
                    if (value >= 1000000000) return (value / 1000000000).toFixed(1) + ' tỷ';
                    if (value >= 1000000) return (value / 1000000).toFixed(1) + ' tr';
                    if (value >= 1000) return Math.round(value / 1000) + 'k';
                    return value;
                }
            }
        });
    });
</script>
@endpush
