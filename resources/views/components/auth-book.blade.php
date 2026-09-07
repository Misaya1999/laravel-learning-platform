@props(['mode' => 'login'])
<div class="auth-book-wrap">
    <a href="{{ route('site.home') }}" class="auth-home-link">← Về trang chủ</a>
    <div class="auth-book auth-book--{{ $mode }} {{ $errors->any() ? 'has-auth-errors' : '' }}" data-auth-book>
        <aside class="auth-book-intro auth-book-base-intro">
            <a href="{{ route('site.home') }}" class="auth-book-logo"><img src="{{ asset('Site/images/logo-admin-light.png') }}" alt="KhoaHocPlus"></a>
            <div class="auth-book-intro-copy" data-auth-intro>
                <span class="auth-book-kicker">Không gian học tập của bạn</span>
                <h1>{{ $mode === 'login' ? 'Chào mừng bạn trở lại.' : 'Bắt đầu một hành trình mới.' }}</h1>
                <p>{{ $mode === 'login' ? 'Tiếp tục những bài học còn dang dở và tiến gần hơn đến mục tiêu của bạn.' : 'Tạo tài khoản để lưu khóa học, theo dõi tiến độ và học tập theo cách của riêng bạn.' }}</p>
            </div>
            <div class="auth-book-quote">“Mỗi trang sách mở ra một cách nhìn mới.”</div>
        </aside>
        <section class="auth-book-page auth-book-register-page">
            @include('auth._register-form', ['mode' => $mode])
        </section>
        <div class="auth-book-leaf">
            <div class="auth-book-page-edge" aria-hidden="true"></div>
            <section class="auth-book-page auth-book-leaf-face auth-book-leaf-front">
                @include('auth._login-form', ['mode' => $mode])
            </section>
            <aside class="auth-book-intro auth-book-leaf-face auth-book-leaf-back">
                <a href="{{ route('site.home') }}" class="auth-book-logo"><img src="{{ asset('Site/images/logo-admin-light.png') }}" alt=""></a>
                <div class="auth-book-intro-copy">
                    <span class="auth-book-kicker">Không gian học tập của bạn</span>
                    <h1>Bắt đầu một hành trình mới.</h1>
                    <p>Tạo tài khoản để lưu khóa học, theo dõi tiến độ và học tập theo cách của riêng bạn.</p>
                </div>
                <div class="auth-book-quote">“Mỗi trang sách mở ra một cách nhìn mới.”</div>
            </aside>
        </div>
        <div class="auth-book-spine" aria-hidden="true"></div>
    </div>
</div>
