<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleController extends Controller
{
    public function redirect(Request $request): SymfonyRedirectResponse
    {
        if (! filled(config('services.google.client_id')) || ! filled(config('services.google.client_secret'))) {
            return redirect()->back()->withErrors(['google' => 'Đăng nhập Google chưa được cấu hình.']);
        }

        if ($request->boolean('accept_terms')) {
            $request->session()->put('google_terms_accepted', true);
        } else {
            $request->session()->forget('google_terms_accepted');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->route('login')->withErrors(['google' => 'Không thể đăng nhập bằng Google. Vui lòng thử lại.']);
        }

        $email = mb_strtolower(trim((string) $googleUser->getEmail()));
        $emailVerified = filter_var($googleUser->user['email_verified'] ?? true, FILTER_VALIDATE_BOOL);
        if ($email === '' || ! $emailVerified) {
            return redirect()->route('login')->withErrors(['google' => 'Google không cung cấp email đã được xác minh.']);
        }

        $termsAccepted = $request->session()->pull('google_terms_accepted', false);
        $user = User::where('google_id', $googleUser->getId())->orWhere('email', $email)->first();
        if (! $user && ! $termsAccepted) {
            return redirect()->route('register')->withErrors(['accept_terms' => 'Vui lòng đồng ý với điều khoản trước khi tạo tài khoản bằng Google.']);
        }

        if ($user) {
            $user->forceFill(['google_id' => $googleUser->getId(), 'email_verified_at' => $user->email_verified_at ?: now()])->save();
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'terms_version' => config('business.policy_version'),
                'terms_accepted_at' => now(),
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        if ($user->role === 'admin') {
            return redirect()->intended(route('Admin.Dashboard'));
        }

        $request->session()->forget('url.intended');
        return redirect()->route('site.home');
    }
}
