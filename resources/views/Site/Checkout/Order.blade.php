@extends('Site.Layout.Index')
@section('title', 'Đơn hàng ' . $order->code . ' — KhoaHocPlus')

@section('content')
<section class="checkout-section"><div class="site-container order-result">
    @if(session('success'))<div class="site-alert site-alert-success">{{ session('success') }}</div>@endif
    @if(session('info'))<div class="site-alert">{{ session('info') }}</div>@endif
    @if(session('payment_setup_error'))<div class="site-alert site-alert-error">{{ session('payment_setup_error') }}</div>@endif
    @if($errors->any())<div class="site-alert site-alert-error">{{ $errors->first() }}</div>@endif
    <span class="order-result-icon">{{ $order->status === 'paid' ? '✓' : '⌛' }}</span>
    <span class="eyebrow coral-text">Đơn hàng {{ $order->code }}</span>
    <h1>{{ $order->status === 'paid' ? 'Đã thanh toán' : 'Đang chờ thanh toán' }}</h1>
    <p>{{ $order->status === 'paid' ? 'Khóa học đã được kích hoạt trong tài khoản của bạn.' : ($order->payment_method === 'payos' ? 'Thanh toán VietQR sẽ được xác nhận tự động và kích hoạt khóa học ngay sau khi ngân hàng báo thành công.' : 'Vui lòng chuyển khoản và chờ admin xác nhận. Khóa học chỉ được kích hoạt sau khi thanh toán thành công.') }}</p>
    <div class="order-result-card">
        @foreach ($order->items as $item)<div><span>{{ $item->course_name }}</span><strong>{{ (float) $item->price <= 0 ? 'Miễn phí' : number_format($item->price, 0, ',', '.') . 'đ' }}</strong></div>@endforeach
        <div class="checkout-total"><span>Tổng thanh toán</span><strong>{{ (float) $order->total <= 0 ? 'Miễn phí' : number_format($order->total, 0, ',', '.') . 'đ' }}</strong></div>
        <div><span>Phương thức</span><strong>{{ match($order->payment_method) { 'free' => 'Đăng ký miễn phí', 'payos' => 'VietQR tự động qua payOS', default => 'Chuyển khoản thủ công' } }}</strong></div>
        <div><span>Trạng thái</span><strong>{{ $order->status === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }}</strong></div>
    </div>
    @if($order->status === 'pending' && $order->payment_method === 'payos')
        <div class="payos-payment-card" data-order-status-url="{{ route('site.orders.status',$order) }}">
            <span class="eyebrow coral-text">Thanh toán VietQR tự động</span>
            <h2>Quét QR và nhận khóa học ngay</h2>
            <p>payOS tự động đối soát đúng số tiền và mã đơn. Bạn không cần chụp hoặc gửi biên lai.</p>
            @if($order->checkout_url)
                <a href="{{ $order->checkout_url }}" class="primary-button full-button">Mở mã VietQR <span>→</span></a>
                <small>Trang này tự kiểm tra trạng thái mỗi 5 giây.</small>
            @else
                <form action="{{ route('site.payos.retry',$order) }}" method="POST">@csrf<button class="primary-button full-button">Thử tạo lại VietQR <span>→</span></button></form>
            @endif
            <form action="{{ route('site.payos.fallback',$order) }}" method="POST" class="payos-fallback-form">@csrf @method('PATCH')<button>Chuyển sang chuyển khoản thủ công</button></form>
        </div>
    @endif
    @if($order->status === 'pending' && $order->payment_method === 'bank_transfer')
        <div class="bank-transfer-card">
            <span class="eyebrow coral-text">Thông tin chuyển khoản</span>
            @if(config('payment.bank.name') && config('payment.bank.account_number') && config('payment.bank.account_name'))
                <dl>
                    <div><dt>Ngân hàng</dt><dd>{{ config('payment.bank.name') }}</dd></div>
                    <div><dt>Số tài khoản</dt><dd>{{ config('payment.bank.account_number') }}</dd></div>
                    <div><dt>Chủ tài khoản</dt><dd>{{ config('payment.bank.account_name') }}</dd></div>
                    @if(config('payment.bank.branch'))<div><dt>Chi nhánh</dt><dd>{{ config('payment.bank.branch') }}</dd></div>@endif
                    <div><dt>Số tiền</dt><dd>{{ number_format($order->total,0,',','.') }}đ</dd></div>
                    <div><dt>Nội dung</dt><dd>{{ $order->code }}</dd></div>
                </dl>
                <p>Vui lòng ghi đúng mã đơn hàng trong nội dung chuyển khoản để admin đối soát nhanh hơn.</p>
            @else
                <div class="site-alert site-alert-error">Thông tin ngân hàng chưa được cấu hình. Vui lòng liên hệ {{ config('business.email') }} trước khi chuyển khoản.</div>
            @endif
        </div>
        <div class="receipt-upload-card">
            <span class="eyebrow coral-text">Biên lai thanh toán</span>
            @if($order->receipt_path)
                <p>Đã gửi lúc <strong>{{ $order->receipt_submitted_at?->format('d/m/Y H:i') }}</strong>. Bạn có thể gửi lại nếu đã chọn nhầm ảnh.</p>
                <a href="{{ route('site.orders.receipt.show',$order) }}" target="_blank" class="light-button">Xem biên lai đã gửi</a>
            @else
                <p>Sau khi chuyển khoản, hãy tải ảnh biên lai để admin kiểm tra. Đơn có biên lai sẽ không bị tự hủy.</p>
            @endif
            <form action="{{ route('site.orders.receipt.store',$order) }}" method="POST" enctype="multipart/form-data" class="receipt-upload-form">@csrf<input type="file" name="receipt" accept="image/jpeg,image/png,image/webp" required><button class="primary-button">{{ $order->receipt_path ? 'Gửi lại biên lai' : 'Gửi biên lai' }} <span>→</span></button></form>
            <small>JPG, PNG hoặc WEBP; tối đa 5MB.</small>
        </div>
    @endif
    @if ($order->status === 'pending')
        <form action="{{ route('site.orders.cancel', $order) }}" method="POST" class="cancel-order-form" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?')">
            @csrf
            @method('PATCH')
            <button type="submit" class="cancel-order-button">Hủy đơn hàng</button>
            <small>Chỉ hủy khi bạn chưa chuyển khoản.</small>
        </form>
    @endif
    <div class="order-result-actions"><a href="{{ route('site.orders.index') }}" class="light-button">Lịch sử đơn hàng</a><a href="{{ route('site.my-courses.index') }}" class="primary-button">Khóa học của tôi</a><a href="{{ route('site.course.index') }}" class="light-button">Xem khóa học khác</a></div>
</div></section>
@endsection

@if (session('order_created'))
@push('scripts')<script>localStorage.removeItem('khoahoc_cart'); document.querySelectorAll('[data-cart-count]').forEach(el => el.textContent = '0');</script>@endpush
@endif

@if($order->status === 'pending' && $order->payment_method === 'payos')
@push('scripts')<script>(()=>{const box=document.querySelector('[data-order-status-url]');if(!box)return;const timer=setInterval(async()=>{try{const response=await fetch(box.dataset.orderStatusUrl,{headers:{Accept:'application/json'},cache:'no-store'});if(!response.ok)return;const data=await response.json();if(data.status==='paid'){clearInterval(timer);window.location.reload();}}catch(_){ }},5000);})();</script>@endpush
@endif
