<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KhoaHocPlus')</title>
    <meta name="description" content="@yield('description', 'Khóa học thực chiến, lộ trình rõ ràng và nội dung được cập nhật liên tục.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('Site/css/style.css') }}?v={{ filemtime(public_path('Site/css/style.css')) }}">
</head>
<body class="@yield('body-class')">
    <header class="site-header" id="top">
        <div class="site-container header-inner">
            <a href="{{ route('site.home') }}" class="brand brand-image" aria-label="KhoaHocPlus trang chủ">
                <img src="{{ asset('Site/images/logo-transparent.png') }}" alt="KhoaHocPlus">
            </a>

            <button type="button" class="mobile-menu-button" data-menu-toggle aria-label="Mở menu">☰</button>

            <nav class="main-nav" data-main-menu aria-label="Điều hướng chính">
                <a href="{{ route('site.home') }}">Trang chủ</a>
                <a href="{{ route('site.course.index') }}">Khóa học</a>
                <a href="{{ route('site.blog.index') }}">Blog</a>
                @auth
                    <a href="{{ route('site.my-courses.index') }}">Khóa học của tôi</a>
                @endauth
                <a href="{{ route('site.mentor.index') }}" @class(['is-active' => request()->routeIs('site.mentor.*')])>Giảng viên</a>
                <!-- <a href="{{ route('site.home') }}#benefits">Lợi ích</a> -->
                <div class="mobile-nav-account">
                    @guest
                        <a href="{{ route('login') }}" class="mobile-account-link">Đăng nhập <span>→</span></a>
                    @else
                        @if (auth()->user()->role !== 'admin')
                            <a href="{{ route('site.profile.edit') }}" class="mobile-user-link">
                                <span class="header-user-avatar">
                                    @if (auth()->user()->avatar)
                                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Ảnh đại diện của {{ auth()->user()->name }}">
                                    @else
                                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                    @endif
                                </span>
                                <span><small>Tài khoản</small><strong>{{ Illuminate\Support\Str::limit(auth()->user()->name, 18, '...') }}</strong></span>
                            </a>
                            <button type="button" class="mobile-nav-action mobile-cart-action" data-cart-open aria-label="Mở giỏ hàng">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L20 8H6M10 20a1 1 0 1 1-2 0 1 1 0 0 1 2 0M19 20a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg>
                                <span class="sr-only">Giỏ hàng</span><span class="cart-count" data-cart-count>0</span>
                            </button>
                            <a href="{{ route('site.orders.index') }}" class="mobile-nav-action">Lịch sử đơn hàng</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="mobile-nav-action mobile-logout">Đăng xuất <span>→</span></button>
                        </form>
                    @endguest
                </div>
            </nav>

            <div class="header-actions">
                @auth
                        <div class="notification-center" data-notification-center>
                            <button type="button" class="notification-bell" data-notification-toggle aria-label="Mở thông báo" aria-expanded="false">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                                <span data-notification-count @if($unreadAnnouncementCount < 1) hidden @endif>{{ $unreadAnnouncementCount > 99 ? '99+' : $unreadAnnouncementCount }}</span>
                            </button>
                            <div class="notification-dropdown" data-notification-dropdown hidden>
                                <div class="notification-dropdown-heading"><div><small>Trung tâm thông báo</small><strong>Thông báo mới</strong></div>@if($unreadAnnouncementCount > 0)<form method="POST" action="{{ route('site.announcements.read-all') }}">@csrf @method('PATCH')<button>Đọc tất cả</button></form>@endif</div>
                                <div class="notification-list">
                                    @forelse($headerAnnouncements as $announcement)
                                        <form method="POST" action="{{ route('site.announcements.read', $announcement) }}" class="notification-item {{ $announcement->is_read ? 'is-read' : 'is-unread' }} notification-{{ $announcement->type }}">@csrf @method('PATCH')<button type="submit"><i></i><span><strong>{{ $announcement->title }}</strong><small>{{ $announcement->content }}</small><time>{{ $announcement->published_at?->diffForHumans() ?? $announcement->created_at->diffForHumans() }}</time></span></button></form>
                                    @empty
                                        <div class="notification-empty"><span>✓</span><strong>Bạn đã xem hết</strong><small>Thông báo mới sẽ xuất hiện tại đây.</small></div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    <button type="button" class="cart-button cart-icon-button" data-cart-open aria-label="Mở giỏ hàng">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L20 8H6M10 20a1 1 0 1 1-2 0 1 1 0 0 1 2 0M19 20a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/></svg>
                        <span class="cart-count" data-cart-count>0</span>
                    </button>
                @endauth

                @guest
                    <a href="{{ route('login') }}" class="outline-button">Đăng nhập</a>
                @else
                    @if (auth()->user()->role !== 'admin')
                        <a href="{{ route('site.profile.edit') }}" class="header-user" aria-label="Mở tài khoản của {{ auth()->user()->name }}">
                            <span class="header-user-avatar">
                                @if (auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Ảnh đại diện của {{ auth()->user()->name }}">
                                @else
                                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </span>
                            <span class="header-user-name">{{ Illuminate\Support\Str::limit(auth()->user()->name, 12, '...') }}</span>
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="outline-button">Đăng xuất</button>
                    </form>
                @endguest
            </div>
        </div>
    </header>

    @auth
        @if (auth()->user()->role !== 'admin' && ! auth()->user()->hasVerifiedEmail())
            <div class="email-verification-banner"><div class="site-container"><span>Email của bạn chưa được xác minh. Hãy xác minh để đặt hàng, đánh giá và học.</span><a href="{{ route('verification.notice') }}">Xác minh ngay →</a></div></div>
        @endif
    @endauth

    <main>
        @yield('content')
    </main>
    
    <footer class="site-footer">
        <div class="site-container footer-grid">
            <div class="footer-about">
                <a href="{{ route('site.home') }}" class="brand brand-image footer-brand-image" aria-label="KhoaHocPlus trang chủ">
                    <img src="{{ asset('Site/images/logo-admin-light.png') }}" alt="KhoaHocPlus">
                </a>
                <p>Khóa học thực chiến về kinh tế, AI và lập trình, được xây dựng theo lộ trình rõ ràng và dễ áp dụng.</p>
                <a class="footer-email" href="mailto:{{ config('app.support_email') }}">{{ config('app.support_email') }}</a>
            </div>

            <div class="footer-column">
                <h3>Khám phá</h3>
                <div class="footer-links">
                    <a href="{{ route('site.course.index') }}">Tất cả khóa học</a>
                    <a href="{{ route('site.blog.index') }}">Blog kiến thức</a>
                    <a href="{{ route('site.home') }}#benefits">Lợi ích</a>
                    <a href="{{ route('site.mentor.index') }}">Giảng viên</a>
                    @auth
                        <a href="{{ route('site.my-courses.index') }}">Khóa học của tôi</a>
                        <a href="{{ route('site.orders.index') }}">Lịch sử đơn hàng</a>
                    @endauth
                </div>
            </div>

            <div class="footer-column">
                <h3>Hỗ trợ</h3>
                <div class="footer-links">
                    <a href="mailto:{{ config('app.support_email') }}">Liên hệ qua email</a>
                    @guest
                        <a href="{{ route('login') }}">Đăng nhập</a>
                    @endguest
                    <span>Thứ 2 – Thứ 7</span>
                    <span>08:00 – 18:00</span>
                </div>
            </div>
        </div>
        <div class="site-container footer-bottom">
            <a href="https://www.facebook.com/tuan.anh.345689" target="_blank" rel="noopener noreferrer">© 2026. Phát triển bởi Misaya</a>
            <span class="footer-legal-links"><a href="{{ route('site.legal.terms') }}">Điều khoản sử dụng</a><i>·</i><a href="{{ route('site.legal.privacy') }}">Chính sách bảo mật</a><i>·</i><a href="{{ route('site.legal.payment') }}">Chính sách thanh toán</a></span>
        </div>
    </footer>

    @auth
        <div class="cart-backdrop" data-cart-backdrop></div>
        <aside class="cart-drawer" data-cart-drawer aria-label="Giỏ hàng">
        <div class="cart-drawer-header">
            <div>
                <span class="eyebrow">Lựa chọn của bạn</span>
                <h2>Giỏ hàng</h2>
                <a href="{{ route('site.orders.index') }}" class="cart-order-history">Xem lịch sử đơn hàng <span>→</span></a>
            </div>
            <button type="button" class="icon-button" data-cart-close aria-label="Đóng giỏ hàng">×</button>
        </div>
        <div class="cart-items" data-cart-items></div>
        <div class="cart-empty" data-cart-empty>
            <span>＋</span>
            <h3>Giỏ hàng đang trống</h3>
            <p>Chọn một khóa học để bắt đầu hành trình.</p>
        </div>
        <div class="cart-summary" data-cart-summary hidden>
            <div><span>Tạm tính</span><strong data-cart-total>0đ</strong></div>
            <button type="button" class="primary-button full-button" data-cart-checkout data-checkout-url="{{ route('site.checkout.show') }}">
                Tiến hành thanh toán
            </button>
            <p class="cart-feedback" data-cart-feedback hidden></p>
        </div>
        </aside>
    @endauth

    <script src="{{ asset('Site/js/app.js') }}?v={{ filemtime(public_path('Site/js/app.js')) }}"></script>
    @stack('scripts')
</body>
</html>
