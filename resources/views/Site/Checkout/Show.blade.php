@extends('Site.Layout.Index')
@section('title', 'Thanh toán — KhoaHocPlus')
@php($isFreeOrder = (float) $courses->sum('price') <= 0)

@section('content')
<section class="checkout-section">
    <div class="site-container">
        <a href="{{ route('site.course.index') }}" class="back-link">← Tiếp tục chọn khóa học</a>
        <div class="checkout-heading"><span class="eyebrow coral-text">Xác nhận đơn hàng</span><h1>{{ $isFreeOrder ? 'Đăng ký miễn phí' : 'Thanh toán' }}</h1><p>Kiểm tra khóa học và thông tin trước khi xác nhận.</p></div>
        @if ($errors->any())<div class="site-alert site-alert-error">{{ $errors->first() }}</div>@endif
        <form action="{{ route('site.checkout.store') }}" method="POST" class="checkout-layout">
            @csrf
            <div class="checkout-main">
                <div class="checkout-card">
                    <h2>Khóa học đã chọn</h2>
                    @foreach ($courses as $courseItem)
                        <div class="checkout-course">
                            <div class="checkout-course-image">@if ($courseItem->image)<img src="{{ asset('storage/' . $courseItem->image) }}" alt="{{ $courseItem->name }}">@else<span>{{ str_pad((string) $courseItem->id, 2, '0', STR_PAD_LEFT) }}</span>@endif</div>
                            <div><strong>{{ $courseItem->name }}</strong><small>Quyền truy cập {{ $courseItem->accessPeriodLabel() }}</small></div>
                            <b>{{ (float) $courseItem->price <= 0 ? 'Miễn phí' : number_format($courseItem->price, 0, ',', '.') . 'đ' }}</b>
                            <input type="hidden" name="course_ids[]" value="{{ $courseItem->id }}">
                        </div>
                    @endforeach
                </div>
                <div class="checkout-card">
                    <h2>Thông tin người mua</h2>
                    <dl class="checkout-user-info"><div><dt>Họ tên</dt><dd>{{ auth()->user()->name }}</dd></div><div><dt>Email</dt><dd>{{ auth()->user()->email }}</dd></div><div><dt>Số điện thoại</dt><dd>{{ auth()->user()->phone ?: 'Chưa cập nhật' }}</dd></div></dl>
                </div>
            </div>
            <aside class="checkout-summary">
                <span class="eyebrow coral-text">Tóm tắt đơn hàng</span>
                <div><span>Tạm tính</span><strong>{{ $isFreeOrder ? 'Miễn phí' : number_format($courses->sum('price'), 0, ',', '.') . 'đ' }}</strong></div>
                <div><span>Giảm giá</span><strong>0đ</strong></div>
                <div class="checkout-total"><span>Tổng cộng</span><strong>{{ $isFreeOrder ? 'Miễn phí' : number_format($courses->sum('price'), 0, ',', '.') . 'đ' }}</strong></div>
                <label class="checkout-terms-consent">
                    <input type="checkbox" name="accept_terms" value="1" @checked(old('accept_terms')) required>
                    <span>Tôi đã đọc và đồng ý với <a href="{{ route('site.legal.terms') }}" target="_blank">Điều khoản sử dụng</a>, <a href="{{ route('site.legal.privacy') }}" target="_blank">Chính sách bảo mật</a> và <a href="{{ route('site.legal.payment') }}" target="_blank">Chính sách thanh toán</a>.</span>
                </label>
                @if ($isFreeOrder)
                    <input type="hidden" name="payment_method" value="free">
                    <button type="submit" class="primary-button full-button">Đăng ký miễn phí <span>→</span></button>
                    <small>Khóa học sẽ được kích hoạt ngay trong tài khoản của bạn.</small>
                @else
                    <label for="payment-method">Phương thức thanh toán</label>
                    <select id="payment-method" name="payment_method" required><option value="payos">VietQR tự động qua payOS</option><option value="bank_transfer">Chuyển khoản thủ công</option></select>
                    <button type="submit" class="primary-button full-button">Tạo đơn hàng <span>→</span></button>
                    <small>VietQR tự động sẽ kích hoạt khóa học ngay sau khi ngân hàng xác nhận. Chuyển khoản thủ công vẫn cần admin kiểm tra.</small>
                @endif
            </aside>
        </form>
    </div>
</section>
@endsection
