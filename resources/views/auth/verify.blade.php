@extends('Site.Layout.Index')

@section('title', 'Xác minh email — KhoaHocPlus')

@section('content')
<section class="verification-section"><div class="site-container verification-card">
    <span class="verification-icon">✉</span>
    <span class="eyebrow coral-text">Bảo vệ tài khoản</span>
    <h1>Xác minh email</h1>
    <p>Một liên kết xác minh đã được gửi tới <strong>{{ auth()->user()->email }}</strong>. Hãy mở email và bấm vào liên kết trước khi đặt hàng, đánh giá hoặc bắt đầu học.</p>
    @if (session('resent'))<div class="site-alert site-alert-success">Liên kết xác minh mới đã được gửi. Vui lòng kiểm tra cả thư mục spam.</div>@endif
    <form method="POST" action="{{ route('verification.resend') }}">@csrf<button type="submit" class="primary-button">Gửi lại liên kết <span>→</span></button></form>
    <a href="{{ route('site.home') }}" class="back-link">← Tiếp tục xem website</a>
</div></section>
@endsection
