@extends('Site.Layout.Index')

@section('title', 'Tài khoản của tôi — KhoaHocPlus')
@section('description', 'Cập nhật thông tin cá nhân và bảo mật tài khoản KhoaHocPlus.')

@section('content')
    <section class="profile-hero">
        <div class="site-container profile-heading">
            <div>
                <span class="eyebrow"><i></i> Không gian cá nhân</span>
                <h1>Tài khoản<br><em>của tôi.</em></h1>
            </div>
            <div class="profile-heading-actions">
                <a href="{{ route('site.orders.index') }}" class="outline-button">Lịch sử đơn hàng</a>
                <a href="{{ route('site.my-courses.index') }}" class="outline-button">Khóa học của tôi →</a>
            </div>
        </div>
    </section>

    <section class="profile-section">
        <div class="site-container profile-layout">
            <aside class="profile-summary">
                <div class="profile-avatar">
                    @if ($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Ảnh đại diện của {{ $user->name }}">
                    @else
                        <span>{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <span class="profile-role">Thành viên KhoaHocPlus</span>
                <h2 title="{{ $user->name }}">{{ Illuminate\Support\Str::limit($user->name, 12, '...') }}</h2>
                <p>{{ $user->email }}</p>
                <dl>
                    <div><dt>Ngày tham gia</dt><dd>{{ $user->created_at?->format('d/m/Y') }}</dd></div>
                    <div><dt>Trạng thái</dt><dd>{{ $user->status === 'active' ? 'Đang hoạt động' : ucfirst($user->status) }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $user->hasVerifiedEmail() ? 'Đã xác minh' : 'Chưa xác minh' }}</dd></div>
                </dl>
            </aside>

            <div class="profile-form-card">
                <section class="personal-learning-overview" aria-labelledby="profile-learning-title">
                    <div class="learning-progress-ring" style="--progress: {{ $overallProgress }}">
                        <span><strong>{{ $overallProgress }}%</strong><small>hoàn thành</small></span>
                    </div>
                    <div class="personal-learning-copy">
                        <span class="eyebrow coral-text">Tiến độ của bạn</span>
                        <h2 id="profile-learning-title">Mỗi bài học là một bước tiến.</h2>
                        <p>Đã hoàn thành <strong>{{ $completedLessonCount }}/{{ $totalLessonCount }}</strong> bài học trong các khóa đã đăng ký.</p>
                        @if ($recentLessonView)
                            <p class="last-learning-line">Vừa xem: <strong>{{ $recentLessonView->lesson->title }}</strong> · {{ $recentLessonView->lesson->section->course->name }}</p>
                        @endif
                    </div>
                    @if ($suggestedEnrollment)
                        @php
                            $continueLesson = $recentLessonView?->lesson?->section?->course_id === $suggestedEnrollment->course_id
                                ? $recentLessonView->lesson
                                : null;
                        @endphp
                        <a href="{{ route('site.learning.show', ['course' => $suggestedEnrollment->course, 'lesson' => $continueLesson]) }}" class="primary-button learning-continue-button">
                            Tiếp tục học <span>→</span>
                        </a>
                    @endif
                </section>

                <div class="profile-form-heading">
                    <span class="eyebrow coral-text">Thông tin cá nhân</span>
                    <h2>Cập nhật hồ sơ</h2>
                    <p>Những thông tin này được sử dụng cho tài khoản học tập của bạn.</p>
                </div>

                @if (session('success'))
                    <div class="site-alert site-alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="site-alert site-alert-error">
                        <strong>Vui lòng kiểm tra lại thông tin:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('site.profile.update') }}" method="POST" enctype="multipart/form-data" class="site-profile-form">
                    @csrf
                    @method('PUT')

                    <div class="profile-field-grid">
                        <label class="site-field">
                            <span>Họ và tên</span>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                        </label>

                        <label class="site-field">
                            <span class="site-field-heading">Email <i class="email-status-badge {{ $user->hasVerifiedEmail() ? 'is-verified' : 'is-unverified' }}">{{ $user->hasVerifiedEmail() ? '✓ Đã xác minh' : 'Chưa xác minh' }}</i></span>
                            <input type="email" value="{{ $user->email }}" disabled aria-label="Email cố định của tài khoản">
                            @if (! $user->hasVerifiedEmail())<small class="email-verification-help">Email chưa được xác minh. <a href="{{ route('verification.notice') }}">Xác minh ngay →</a></small>@else<small class="email-verification-help">Email đăng nhập được cố định và không thể thay đổi.</small>@endif
                        </label>
                    </div>

                    <label class="site-field">
                        <span>Số điện thoại</span>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20" autocomplete="tel" placeholder="Chưa cập nhật">
                    </label>

                    <label class="site-field site-file-field">
                        <span>Ảnh đại diện</span>
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif">
                        <small>JPG, PNG hoặc GIF; dung lượng tối đa 1MB.</small>
                    </label>

                    <div class="profile-password-block">
                        <div>
                            <h3>Đổi mật khẩu</h3>
                            <p>Để trống hai ô nếu bạn không muốn thay đổi mật khẩu.</p>
                        </div>

                        <div class="profile-field-grid">
                            <label class="site-field">
                                <span>Mật khẩu mới</span>
                                <span class="password-control">
                                    <input type="password" id="profile-password" name="password" minlength="8" autocomplete="new-password">
                                    <button type="button" data-password-toggle="profile-password" aria-label="Hiện hoặc ẩn mật khẩu">👁</button>
                                </span>
                            </label>

                            <label class="site-field">
                                <span>Xác nhận mật khẩu</span>
                                <span class="password-control">
                                    <input type="password" id="profile-password-confirmation" name="password_confirmation" minlength="8" autocomplete="new-password">
                                    <button type="button" data-password-toggle="profile-password-confirmation" aria-label="Hiện hoặc ẩn mật khẩu">👁</button>
                                </span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="primary-button profile-submit">Lưu thay đổi <span>↗</span></button>
                </form>
            </div>
        </div>
    </section>
@endsection
