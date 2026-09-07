<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use App\Services\AdminLiveStatusService;


class UserController extends Controller
{
    public function user(Request $request, AdminLiveStatusService $liveStatus)
    {
        $sort = $request->string('sort')->toString();
        $sort = in_array($sort, ['latest', 'oldest', 'name_asc', 'name_desc', 'courses_desc'], true)
            ? $sort
            : 'latest';

        $userQuery = User::with([
            'enrollments' => fn ($query) => $query
                ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
                ->with('course:id,name'),
        ])->withCount([
            'enrollments as courses_count' => fn ($query) => $query
                ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED]),
        ]);

        if ($search = trim($request->string('search')->toString())) {
            $userQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($request->email_status === 'verified') {
            $userQuery->where(fn ($query) => $query->where('role', 'admin')->orWhereNotNull('email_verified_at'));
        }
        if ($request->email_status === 'unverified') {
            $userQuery->where('role', '!=', 'admin')->whereNull('email_verified_at');
        }

        $userQuery->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END");

        match ($sort) {
            'oldest' => $userQuery->oldest(),
            'name_asc' => $userQuery->orderBy('name'),
            'name_desc' => $userQuery->orderByDesc('name'),
            'courses_desc' => $userQuery->orderByDesc('courses_count'),
            default => $userQuery->latest(),
        };

        $user = $userQuery->paginate(15)->withQueryString();
        $presenceSummary = $liveStatus->snapshot();
        $onlineUserIds = collect($presenceSummary['online_user_ids']);

        return view('Admin.User', compact('user', 'onlineUserIds', 'presenceSummary'));
    }


    public function user_delete(User $user)
    {
        // Không cho admin đang đăng nhập tự xóa chính mình
        if ($user->id === auth()->id()) {
            return back()->withErrors('Bạn không thể xóa tài khoản đang đăng nhập');
        }
       
        $user->delete();

        return redirect()->route('Admin.User')->with('success', 'Xóa người dùng thành công.');
    }

    public function updateStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors('Bạn không thể khóa tài khoản đang đăng nhập.');
        }

        $user->status = $user->status === 'blocked' ? 'active' : 'blocked';
        $user->save();

        if ($user->status === 'blocked') {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $message = $user->status === 'blocked'
            ? 'Khóa tài khoản thành công.'
            : 'Mở khóa tài khoản thành công.';

        return back()->with('success', $message);
    }

    public function sendPasswordReset(User $user)
    {
        $status = Password::broker()->sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors('Liên kết vừa được gửi gần đây. Vui lòng thử lại sau một phút.');
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors('Không thể gửi email đặt lại mật khẩu. Vui lòng thử lại.');
        }

        return back()->with('success', 'Đã gửi liên kết đặt lại mật khẩu tới ' . $user->email . '.');
    }
}
