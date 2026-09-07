<div class="auth-form-heading"><span>Tạo tài khoản</span><h2>Tham gia KhoaHocPlus</h2><p>Chỉ mất một phút để bắt đầu hành trình học tập.</p></div>
@if($mode === 'register' && $errors->any())
    <div class="auth-toast auth-toast--error" role="alert" data-auth-toast>
        <span class="auth-toast-icon" aria-hidden="true">!</span>
        <span class="auth-toast-content"><strong>Chưa thể tạo tài khoản</strong><span>{{ $errors->first() }}</span></span>
        <button type="button" class="auth-toast-close" data-auth-toast-close aria-label="Đóng thông báo">×</button>
        <span class="auth-toast-progress" aria-hidden="true"></span>
    </div>
@endif
<form method="POST" action="{{ route('register') }}" class="auth-book-form auth-book-form--register" data-auth-form>
    @csrf
    <div class="auth-field"><label for="register-name">Họ và tên</label><input id="register-name" type="text" class="{{ $mode === 'register' && $errors->has('name') ? 'is-invalid' : '' }}" name="name" value="{{ $mode === 'register' ? old('name') : '' }}" placeholder="Nguyễn Văn A" required autocomplete="name" @if($mode === 'register') autofocus @endif></div>
    <div class="auth-field"><label for="register-email">Email</label><input id="register-email" type="email" class="{{ $mode === 'register' && $errors->has('email') ? 'is-invalid' : '' }}" name="email" value="{{ $mode === 'register' ? old('email') : '' }}" placeholder="ban@example.com" required autocomplete="email"></div>
    <div class="auth-field-grid">
        <div class="auth-field"><label for="register-password">Mật khẩu</label><div class="auth-password-wrap"><input id="register-password" type="password" class="{{ $mode === 'register' && $errors->has('password') ? 'is-invalid' : '' }}" name="password" placeholder="Tối thiểu 8 ký tự" required minlength="8" autocomplete="new-password" data-password-input><button type="button" class="auth-password-toggle" data-password-toggle="register-password" aria-label="Hiện mật khẩu" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/></svg></button></div><small class="auth-caps-warning" data-caps-warning hidden>Caps Lock đang bật</small></div>
        <div class="auth-field"><label for="register-password-confirm">Nhập lại mật khẩu</label><div class="auth-password-wrap"><input id="register-password-confirm" type="password" class="{{ $mode === 'register' && $errors->has('password_confirmation') ? 'is-invalid' : '' }}" name="password_confirmation" placeholder="Nhập lại mật khẩu" required minlength="8" autocomplete="new-password" data-password-input><button type="button" class="auth-password-toggle" data-password-toggle="register-password-confirm" aria-label="Hiện mật khẩu" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/></svg></button></div><small class="auth-caps-warning" data-caps-warning hidden>Caps Lock đang bật</small></div>
    </div>
    <div class="auth-password-strength" data-password-strength data-level="empty" aria-live="polite">
        <div class="auth-password-strength-track" aria-hidden="true"><span></span></div>
        <small><span data-password-strength-label>Độ mạnh mật khẩu</span><span>Tối thiểu 8 ký tự</span></small>
    </div>
    <label class="auth-terms-check"><input type="checkbox" name="accept_terms" value="1" @checked($mode === 'register' && old('accept_terms')) data-register-terms required><span>Tôi đồng ý với <a href="{{ route('site.legal.terms') }}" target="_blank">Điều khoản sử dụng</a> và <a href="{{ route('site.legal.privacy') }}" target="_blank">Chính sách bảo mật</a>.</span></label>
    <button type="submit" class="auth-submit" data-auth-submit data-loading-text="Đang tạo tài khoản…"><span class="auth-submit-label">Tạo tài khoản</span><span aria-hidden="true">→</span></button>
</form>
<div class="auth-divider"><span>hoặc</span></div>
<a href="{{ route('auth.google.redirect') }}" class="auth-google-button" data-google-auth><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1Z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.94-6.16-4.54H2.18v2.84A11 11 0 0 0 12 23Z"/><path fill="#FBBC05" d="M5.84 14.09A6.6 6.6 0 0 1 5.5 12c0-.73.13-1.43.34-2.09V7.07H2.18A11 11 0 0 0 1 12c0 1.77.42 3.44 1.18 4.93l3.66-2.84Z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15A10.6 10.6 0 0 0 12 1 11 11 0 0 0 2.18 7.07l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38Z"/></svg><span>Đăng ký bằng Google</span></a>
<p class="auth-switch">Đã có tài khoản? <a href="{{ route('login') }}" data-book-switch="login">Quay lại đăng nhập</a></p>
