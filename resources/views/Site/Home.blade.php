@extends('Site.Layout.Index')

@section('title', 'KhoaHocPlus — Học đúng, làm được')
@section('body-class', 'home-aura-page')

@section('content')
    <section class="hero-section">
        <div class="site-container hero-grid">
            <div class="hero-copy">
                <span class="eyebrow"><i></i> Học đúng — Làm được</span>
                <h1 class="hero-outline-title">
                    <span class="sr-only">Mở khóa tiềm năng của bạn.</span>
                    <svg viewBox="0 0 650 270" role="img" aria-hidden="true">
                        <g class="hero-title-base">
                            <text x="5" y="78">MỞ KHÓA</text>
                            <text x="5" y="164">TIỀM NĂNG</text>
                            <text x="5" y="250">CỦA BẠN.</text>
                        </g>
                        <g class="hero-title-runner">
                            <text x="5" y="78">MỞ KHÓA</text>
                            <text x="5" y="164">TIỀM NĂNG</text>
                            <text x="5" y="250">CỦA BẠN.</text>
                        </g>
                    </svg>
                </h1>
                <p>Khóa học thực chiến, lộ trình rõ ràng và kiến thức có thể áp dụng ngay vào công việc.</p>
                <div class="hero-actions">
                    <a href="#courses" class="primary-button">Khám phá khóa học <span>↗</span></a>
                    <a href="#benefits" class="play-link"><span>▶</span> Xem cách học</a>
                </div>
                <div class="hero-proof">
                    <div class="avatar-stack"><b>KT</b><b>AI</b><b>LP</b></div>
                    <div><strong>Nhiều chủ đề thực tiễn</strong><span>Kinh tế · AI · Lập trình</span></div>
                </div>
            </div>

            <div class="hero-art-stage">
                <div class="hero-water-ripples" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
                <div class="hero-art" aria-label="Không gian học tập sáng tạo">
                    <div class="hero-blue"></div>
                    <div class="hero-coral"></div>
                    <div class="paper-card"><b>LEARN</b><span>＋</span></div>
                    <div class="quote-card"><span>“</span><strong>Tiến bộ<br>mỗi ngày.</strong><small>01 — {{ date('Y') }}</small></div>
                    <div class="creator-tag">THỰC<br>CHIẾN</div>
                    <div class="spark">✦</div>
                </div>
            </div>
        </div>
    </section>

    <div class="topic-marquee" aria-label="Chủ đề khóa học">
        <div>
            @forelse ($category as $categoryItem)
                <a href="{{ route('site.course.index', ['category' => $categoryItem->id]) }}">
                    {{ mb_strtoupper($categoryItem->name) }}
                    <small>{{ $categoryItem->courses_count }}</small>
                </a>
                @if (! $loop->last)<b>✦</b>@endif
            @empty
                <span>CHƯA CÓ CATEGORY</span>
            @endforelse
        </div>
    </div>

    <section class="courses-section" id="courses">
        <div class="site-container">
            <div class="section-heading-row">
                <div><span class="eyebrow coral-text">Mới cập nhật</span><h2>Khóa học nổi bật</h2></div>
                <a href="{{ route('site.course.index') }}" class="arrow-link">Xem tất cả ↗</a>
            </div>

            <div class="course-grid">
                @forelse ($course as $courseItem)
                    @include('Site.Partials.CourseCard', ['courseItem' => $courseItem])
                @empty
                    <div class="empty-panel">
                        <span>＋</span><h3>Khóa học đang được chuẩn bị</h3><p>Hãy quay lại sau để xem nội dung mới.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    @auth
    <section class="my-courses-section" id="my-courses">
        <div class="site-container">
            <div class="section-heading-row light-heading">
                <div><span class="eyebrow">Không gian học tập</span><h2>Khóa học của tôi</h2></div>
                <a href="{{ route('site.my-courses.index') }}" class="outline-pill">Xem tất cả ↗</a>
            </div>
            @if ($myCourse->isNotEmpty())
                <div class="home-learning-grid">
                    @foreach ($myCourse as $myCourseItem)
                        @php
                            $homeExpiresAt = $myCourseItem->pivot->expires_at
                                ? Illuminate\Support\Carbon::parse($myCourseItem->pivot->expires_at)
                                : null;
                            $homeCourseExpired = $homeExpiresAt?->isPast() ?? false;
                            $homeRemainingDays = $homeExpiresAt
                                ? max(0, (int) ceil(($homeExpiresAt->getTimestamp() - now()->getTimestamp()) / 86400))
                                : null;
                        @endphp
                        <article class="home-learning-card">
                            <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <small>{{ $myCourseItem->category?->name ?? 'Khóa học' }}</small>
                                <h3>{{ $myCourseItem->name }}</h3>
                                <p>
                                    {{ $myCourseItem->lessons_count }} bài học ·
                                    @if ($homeExpiresAt)
                                        @if ($homeCourseExpired)
                                            Hết hạn {{ $homeExpiresAt->format('d/m/Y') }}
                                        @else
                                            Còn {{ $homeRemainingDays }} ngày · đến {{ $homeExpiresAt->format('d/m/Y') }}
                                        @endif
                                    @else
                                        Đăng ký {{ Illuminate\Support\Carbon::parse($myCourseItem->pivot->enrolled_at)->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>
                            <a href="{{ $homeCourseExpired ? route('site.course.show', $myCourseItem) : route('site.learning.show', $myCourseItem) }}" aria-label="{{ $homeCourseExpired ? 'Gia hạn' : 'Tiếp tục học' }} {{ $myCourseItem->name }}">→</a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="my-course-empty">
                    <span>＋</span>
                    <div><h3>Chưa có khóa học được đăng ký</h3><p>Chọn một khóa học phù hợp để bắt đầu hành trình học tập.</p></div>
                    <a href="#courses" class="light-button">Khám phá khóa học ↗</a>
                </div>
            @endif
        </div>
    </section>
    @endauth

    <section class="benefits-section" id="benefits">
        <div class="site-container">
            <div class="benefits-intro"><span class="eyebrow coral-text">Vì sao chọn chúng tôi</span><h2>Học để <em>tiến xa.</em></h2></div>
            <div class="benefit-grid">
                <article><span>01</span><h3>Nội dung thực chiến</h3><p>Học từ ví dụ thực tế và áp dụng ngay vào công việc.</p></article>
                <article><span>02</span><h3>Lộ trình rõ ràng</h3><p>Chương và bài học được sắp xếp theo mức độ tiến bộ.</p></article>
                <article><span>03</span><h3>Học chủ động</h3><p>Video, bài đọc và bài tập kết hợp trong một khóa học.</p></article>
            </div>
        </div>
    </section>

    <section class="teacher-section mentor-showcase" id="teacher">
        <div class="site-container">
            <div class="mentor-section-heading">
                <div><span class="eyebrow coral-text">Giảng viên đồng hành</span><h2>Học cùng người có kinh nghiệm.</h2></div>
                <a href="{{ route('site.mentor.index') }}" class="outline-button">Xem thêm mentor khác <span>→</span></a>
            </div>
            @if ($mainMentor)
                <article class="main-mentor-card">
                    <div class="main-mentor-portrait">
                        @if ($mainMentor->avatar)
                            <img src="{{ asset('storage/' . $mainMentor->avatar) }}" alt="{{ $mainMentor->name }}">
                        @else
                            <span>{{ mb_strtoupper(mb_substr($mainMentor->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="main-mentor-copy">
                        @if ($mainMentor->specialty)<span class="mentor-field">{{ $mainMentor->specialty }}</span>@endif
                        <h3>{{ $mainMentor->name }}</h3>
                        @if ($mainMentor->bio && mb_strtolower(trim($mainMentor->bio)) !== 'mentor')<p>{{ $mainMentor->bio }}</p>@endif
                        @if ($mainMentor->philosophy)<blockquote>“{{ $mainMentor->philosophy }}”</blockquote>@endif
                        <div class="mentor-facts"><span><strong>{{ $mainMentor->courses_count }}</strong> khóa học đang giảng dạy</span></div>
                        <a href="{{ route('site.course.index', ['mentor' => $mainMentor->id]) }}" class="primary-button">Xem khóa học của mentor <span>↗</span></a>
                    </div>
                    @if ($mainMentor->certificates->isNotEmpty())
                        <div class="main-mentor-certificates">
                            <div class="certificate-heading"><h4>Thành tích nổi bật</h4></div>
                            <div class="certificate-carousel" data-certificate-carousel>
                                <div class="certificate-carousel-stage">
                                @foreach($mainMentor->certificates as $certificate)
                                    <button type="button" class="certificate-slide" data-certificate-slide data-certificate-image="{{ asset('storage/' . $certificate->image) }}" aria-label="Xem ảnh chứng chỉ {{ $loop->iteration }}">
                                        <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                        <img src="{{ asset('storage/' . $certificate->image) }}" alt="Chứng chỉ của {{ $mainMentor->name }}">
                                        <i>Phóng to ↗</i>
                                    </button>
                                @endforeach
                                </div>
                                @if ($mainMentor->certificates->count() > 1)
                                    <div class="certificate-carousel-controls">
                                        <button type="button" data-certificate-prev aria-label="Chứng chỉ trước">‹</button>
                                        <div>
                                            @foreach($mainMentor->certificates as $certificate)
                                                <button type="button" data-certificate-dot="{{ $loop->index }}" aria-label="Đi đến chứng chỉ {{ $loop->iteration }}"></button>
                                            @endforeach
                                        </div>
                                        <button type="button" data-certificate-next aria-label="Chứng chỉ tiếp theo">›</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </article>
            @endif

        </div>
    </section>
    <div class="certificate-lightbox" data-certificate-lightbox hidden><button type="button" data-certificate-close aria-label="Đóng ảnh chứng chỉ">×</button><img src="" alt="Ảnh chứng chỉ phóng lớn" data-certificate-preview></div>
@endsection
