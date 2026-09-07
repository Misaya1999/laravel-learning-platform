@extends('layouts.app')

@section('content')
<div class="auth-book-wrap">
    <a href="{{ route('site.home') }}" class="auth-home-link">← Về trang chủ</a>
    <div class="auth-book auth-recovery-book">
        <aside class="auth-book-intro auth-book-base-intro">
            <a href="{{ route('site.home') }}" class="auth-book-logo"><img src="{{ asset('Site/images/logo-admin-light.png') }}" alt="KhoaHocPlus"></a>
            <div class="auth-book-intro-copy"><span class="auth-book-kicker">Bảo vệ tài khoản</span><h1>Bắt đầu lại với mật khẩu mới.</h1><p>Chọn mật khẩu đủ mạnh để tiếp tục học tập một cách an toàn.</p></div>
            <div class="auth-book-quote">“Tài khoản an toàn giúp hành trình học tập liền mạch hơn.”</div>
        </aside>
        <section class="auth-book-page auth-recovery-page">
            <div class="auth-form-heading"><span>Mật khẩu mới</span><h2>Đặt lại mật khẩu</h2><p>Nhập email và mật khẩu bạn muốn sử dụng.</p></div>
            @if($errors->has('email'))<div class="auth-alert"><strong>Không thể đặt lại mật khẩu</strong><span>{{ $errors->first('email') }}</span></div>@endif

            <form method="POST" action="{{ route('password.update') }}" class="auth-book-form" data-auth-form>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="auth-field"><label for="reset-email">Email đăng ký</label><input id="reset-email" type="email" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" readonly></div>
                <div class="auth-field"><label for="reset-password">Mật khẩu mới</label><div class="auth-password-wrap"><input id="reset-password" type="password" name="password" placeholder="Tối thiểu 8 ký tự" class="{{ $errors->has('password') ? 'is-invalid' : '' }}" required minlength="8" autocomplete="new-password" data-password-input><button type="button" class="auth-password-toggle" data-password-toggle="reset-password" aria-label="Hiện mật khẩu" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/></svg></button></div>@error('password')<span class="auth-error">{{ $message }}</span>@enderror<small class="auth-caps-warning" data-caps-warning hidden>Caps Lock đang bật</small></div>
                <div class="auth-field"><label for="reset-password-confirmation">Nhập lại mật khẩu</label><div class="auth-password-wrap"><input id="reset-password-confirmation" type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu mới" required minlength="8" autocomplete="new-password" data-password-input><button type="button" class="auth-password-toggle" data-password-toggle="reset-password-confirmation" aria-label="Hiện mật khẩu" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/></svg></button></div><small class="auth-caps-warning" data-caps-warning hidden>Caps Lock đang bật</small></div>
                <button type="submit" class="auth-submit" data-auth-submit data-loading-text="Đang cập nhật mật khẩu…"><span class="auth-submit-label">Cập nhật mật khẩu</span><span aria-hidden="true">→</span></button>
            </form>
            <p class="auth-switch">Quay lại <a href="{{ route('login') }}">trang đăng nhập</a></p>
        </section>
        <div class="auth-book-spine" aria-hidden="true"></div>
    </div>
</div>
@endsection
