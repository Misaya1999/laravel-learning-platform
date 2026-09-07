<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function credentials(Request $request): array
    {
        return [
            $this->username() => $request->input($this->username()),
            'password' => $request->input('password'),
            'status' => 'active',
        ];
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->role === 'admin') {
            return redirect()->intended(route('Admin.Dashboard'));
        }

        // Không dùng intended cho user thường để URL admin từng truy cập
        // trong session không thể kéo họ trở lại khu vực quản trị.
        $request->session()->forget('url.intended');

        return redirect()->route('site.home');
    }
}
