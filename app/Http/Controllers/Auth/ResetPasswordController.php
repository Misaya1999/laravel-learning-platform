<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:10,1')->only('reset');
    }

    protected function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected function validationErrorMessages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu cần tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }

    protected function resetPassword($user, $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('sessions')->where('user_id', $user->id)->delete();

        event(new PasswordReset($user));
        $user->notify(new PasswordChangedNotification());
    }

    protected function sendResetResponse(Request $request, $response): RedirectResponse
    {
        return redirect()->route('login')->with('status', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.');
    }

    protected function sendResetFailedResponse(Request $request, $response): RedirectResponse
    {
        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.',
        ]);
    }
}
