@extends('Site.Layout.Index')

@section('title', 'Lịch sử đơn hàng — KhoaHocPlus')

@section('content')
<section class="profile-hero order-history-hero">
    <div class="site-container profile-heading">
        <div>
            <span class="eyebrow"><i></i> Tài khoản của tôi</span>
            <h1>Lịch sử<br><em>đơn hàng.</em></h1>
        </div>
        <a href="{{ route('site.course.index') }}" class="outline-button">Tiếp tục chọn khóa học →</a>
    </div>
</section>

<section class="order-history-section">
    <div class="site-container">
        @if (session('success'))<div class="site-alert site-alert-success order-history-alert">{{ session('success') }}</div>@endif
        <nav class="order-status-tabs" aria-label="Lọc đơn hàng theo trạng thái">
            <a href="{{ route('site.orders.index') }}" @class(['active' => !in_array($status, ['pending', 'paid', 'cancelled', 'refunded'], true)])>Tất cả <span>{{ $counts->sum() }}</span></a>
            <a href="{{ route('site.orders.index', ['status' => 'pending']) }}" @class(['active' => $status === 'pending'])>Chờ thanh toán <span>{{ $counts['pending'] ?? 0 }}</span></a>
            <a href="{{ route('site.orders.index', ['status' => 'paid']) }}" @class(['active' => $status === 'paid'])>Đã thanh toán <span>{{ $counts['paid'] ?? 0 }}</span></a>
            <a href="{{ route('site.orders.index', ['status' => 'cancelled']) }}" @class(['active' => $status === 'cancelled'])>Đã hủy <span>{{ $counts['cancelled'] ?? 0 }}</span></a>
            <a href="{{ route('site.orders.index', ['status' => 'refunded']) }}" @class(['active' => $status === 'refunded'])>Đã hoàn tiền <span>{{ $counts['refunded'] ?? 0 }}</span></a>
        </nav>

        <div class="order-history-list">
            @forelse ($orders as $order)
                @php
                    $statusInfo = match ($order->status) {
                        'paid' => ['Đã thanh toán', 'paid'],
                        'cancelled' => ['Đã hủy', 'cancelled'],
                        'refunded' => ['Đã hoàn tiền', 'refunded'],
                        default => ['Chờ thanh toán', 'pending'],
                    };
                @endphp
                <article class="order-history-card">
                    <header>
                        <div><small>Mã đơn hàng</small><strong>{{ $order->code }}</strong></div>
                        <div><small>Ngày tạo</small><strong>{{ $order->created_at->format('d/m/Y H:i') }}</strong></div>
                        <span class="order-status order-status-{{ $statusInfo[1] }}">{{ $statusInfo[0] }}</span>
                    </header>
                    <div class="order-history-body">
                        <div class="order-course-summary">
                            @foreach ($order->items->take(2) as $item)
                                <span>{{ $item->course_name }}</span>
                            @endforeach
                            @if ($order->items_count > 2)<small>và {{ $order->items_count - 2 }} khóa học khác</small>@endif
                        </div>
                        <div class="order-history-total">
                            <small>Tổng cộng</small>
                            <strong>{{ (float) $order->total <= 0 ? 'Miễn phí' : number_format($order->total, 0, ',', '.') . 'đ' }}</strong>
                            @if($order->status === 'pending' && $order->receipt_path)<span class="receipt-submitted-label">✓ Đã gửi biên lai</span>@endif
                        </div>
                        <a href="{{ route('site.orders.show', $order) }}" class="primary-button">Xem chi tiết <span>→</span></a>
                    </div>
                </article>
            @empty
                <div class="order-history-empty">
                    <span>＋</span><h2>Chưa có đơn hàng</h2><p>Bạn chưa có đơn hàng phù hợp với trạng thái này.</p>
                    <a href="{{ route('site.course.index') }}" class="primary-button">Khám phá khóa học</a>
                </div>
            @endforelse
        </div>

        @if ($orders->hasPages())<div class="site-pagination">{{ $orders->links() }}</div>@endif
    </div>
</section>
@endsection
