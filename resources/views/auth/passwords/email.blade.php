@extends('layouts.app')

@section('content')
<div class="auth-book-wrap">
    <a href="{{ route('site.home') }}" class="auth-home-link">← Về trang chủ</a>
    <div class="auth-book auth-recovery-book">
        <aside class="auth-book-intro auth-book-base-intro">
            <a href="{{ route('site.home') }}" class="auth-book-logo"><img src="{{ asset('Site/images/logo-admin-light.png') }}" alt="KhoaHocPlus"></a>
            <div class="auth-book-intro-copy"><span class="auth-book-kicker">Khôi phục tài khoản</span><h1>Đừng lo, chúng mình sẽ giúp bạn.</h1><p>Nhập email đã đăng ký để nhận liên kết đặt lại mật khẩu an toàn.</p></div>
            <div class="auth-book-quote">“Chỉ mất một phút để quay lại hành trình học tập.”</div>
        </aside>
        <section class="auth-book-page auth-recovery-page">
            <div class="auth-form-heading"><span>Khôi phục tài khoản</span><h2>Quên mật khẩu?</h2><p>Chúng tôi sẽ gửi hướng dẫn tới email của bạn.</p></div>

            @if(session('status'))<div class="auth-status-message" role="status">{{ session('status') }}</div>@endif

            <form method="POST" action="{{ route('password.email') }}" class="auth-book-form" data-auth-form>
                @csrf
                <div class="auth-field"><label for="recovery-email">Email đăng ký</label><input id="recovery-email" type="email" name="email" value="{{ old('email') }}" placeholder="ban@example.com" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" required autocomplete="email" autofocus>@error('email')<span class="auth-error">{{ $message }}</span>@enderror</div>
                <button type="submit" class="auth-submit" data-auth-submit data-loading-text="Đang gửi hướng dẫn…"><span class="auth-submit-label">Gửi liên kết đặt lại</span><span aria-hidden="true">→</span></button>
            </form>

            <p class="auth-recovery-help">Liên kết có hiệu lực trong {{ config('auth.passwords.users.expire', 60) }} phút. Nếu không thấy email, hãy kiểm tra thư mục spam.</p>
            <p class="auth-switch">Đã nhớ mật khẩu? <a href="{{ route('login') }}">Quay lại đăng nhập</a></p>
        </section>
        <div class="auth-book-spine" aria-hidden="true"></div>
    </div>
</div>
@endsection
