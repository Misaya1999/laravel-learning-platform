@extends('Site.Layout.Index')

@section('title', $course->name . ' — KhoaHocPlus')
@section('description', Illuminate\Support\Str::limit($course->description, 150))

@section('content')
    @php
        $detailCategoryKey = mb_strtolower(Illuminate\Support\Str::ascii($course->category?->name ?? ''));
        $detailTheme = str_contains($detailCategoryKey, 'kinh te') ? 'economy' : (str_contains($detailCategoryKey, 'lap trinh') ? 'programming' : (str_contains($detailCategoryKey, 'ai') || str_contains($detailCategoryKey, 'tri tue') ? 'ai' : 'general'));
        $lessonTitles = $course->sections->flatMap->lessons->pluck('title')->filter()->take(3);
        $learningOutcomes = $lessonTitles->isNotEmpty() ? $lessonTitles : collect(match ($detailTheme) {
            'economy' => ['Hiểu các khái niệm kinh tế cốt lõi', 'Phân tích dữ liệu và tình huống thực tế', 'Ứng dụng kiến thức vào quyết định tài chính'],
            'programming' => ['Nắm vững nền tảng và tư duy lập trình', 'Tự xây dựng sản phẩm thực tế', 'Biết cách đọc lỗi và cải thiện mã nguồn'],
            'ai' => ['Hiểu cách các mô hình AI hoạt động', 'Ứng dụng AI vào công việc thực tế', 'Đánh giá kết quả và sử dụng AI có trách nhiệm'],
            default => ['Nắm vững kiến thức nền tảng', 'Thực hành theo lộ trình rõ ràng', 'Ứng dụng kiến thức vào tình huống thực tế'],
        });
        $audienceText = match ($detailTheme) {
            'economy' => 'Người muốn xây dựng nền tảng kinh tế, tài chính và tư duy phân tích.',
            'programming' => 'Người mới bắt đầu hoặc muốn củng cố kỹ năng xây dựng sản phẩm.',
            'ai' => 'Người muốn hiểu và ứng dụng AI hiệu quả trong học tập, công việc.',
            default => 'Người muốn học có lộ trình và áp dụng kiến thức vào thực tế.',
        };
    @endphp
    <section class="course-detail-hero detail-theme-{{ $detailTheme }}">
        <div class="site-container">
            <a href="{{ route('site.course.index') }}" class="back-link">← Tất cả khóa học</a>
            <div class="detail-hero-grid">
                <div class="detail-copy">
                    <span class="eyebrow"><i></i> {{ $course->category?->name ?? 'Khóa học' }}</span>
                    <h1>{{ $course->name }}</h1>
                    <p>{{ $course->description ?: 'Khóa học được thiết kế theo lộ trình rõ ràng và nội dung thực tế.' }}</p>
                    <div class="detail-stats">
                        <span><strong>{{ $course->sections_count }}</strong> chương</span>
                        <span><strong>{{ $course->lessons_count }}</strong> bài học</span>
                        <span><strong>{{ $course->access_duration }}</strong> {{ $course->access_duration_unit === 'days' ? 'ngày truy cập' : 'tháng truy cập' }}</span>
                        <span><strong>{{ $course->reviews_avg_rating ? number_format($course->reviews_avg_rating, 1) : '—' }}</strong> rating</span>
                        <span><strong>{{ $course->students_count ?? 0 }}</strong> học viên</span>
                    </div>
                </div>
                <div class="detail-poster course-pattern-{{ $detailTheme }}" id="course-preview">
                    @if ($previewLesson)
                        <iframe src="{{ $previewLesson->videoEmbedUrl() }}" title="Video giới thiệu {{ $course->name }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        <span class="preview-video-label">Video xem trước</span>
                    @elseif ($course->image)
                        <img src="{{ asset('storage/' . $course->image) }}" alt="{{ $course->name }}" data-course-image>
                    @else
                        <span class="poster-number">{{ str_pad((string) $course->id, 2, '0', STR_PAD_LEFT) }}</span>
                        <strong>LEARN<br>TO DO.</strong><i>✦</i>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if ($course->mentors->isNotEmpty())
        <section class="course-mentors-section">
            <div class="site-container">
                <div class="course-mentors-heading">
                    <span class="eyebrow coral-text">Người đồng hành</span>
                    <h2>Mentor của khóa học</h2>
                </div>
                <div class="course-mentor-grid">
                    @foreach ($course->mentors as $mentorItem)
                        <article class="course-mentor-card">
                            <div class="course-mentor-avatar">
                                @if ($mentorItem->avatar)
                                    <img src="{{ asset('storage/' . $mentorItem->avatar) }}" alt="{{ $mentorItem->name }}">
                                @else
                                    <span>{{ mb_strtoupper(mb_substr($mentorItem->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div>
                                <small>Mentor</small>
                                <h3>{{ $mentorItem->name }}</h3>
                                <strong class="mentor-specialty">{{ $mentorItem->courses->pluck('category.name')->filter()->unique()->join(' · ') ?: ($course->category?->name ?? 'Giảng viên') }}</strong>
                                @if ($mentorItem->bio && mb_strtolower(trim($mentorItem->bio)) !== 'mentor')
                                    <p>{{ $mentorItem->bio }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="detail-content-section">
        <div class="site-container detail-layout">
            <div class="curriculum-column">
                <div class="course-value-grid">
                    <article class="course-value-card course-value-main"><span>01</span><h2>Bạn sẽ học được gì?</h2><ul>@foreach ($learningOutcomes as $outcome)<li>{{ $outcome }}</li>@endforeach</ul></article>
                    <article class="course-value-card"><span>02</span><h3>Khóa học dành cho ai?</h3><p>{{ $audienceText }}</p></article>
                    <article class="course-value-card"><span>03</span><h3>Yêu cầu trước khi học</h3><p>Không yêu cầu kinh nghiệm chuyên sâu. Bạn chỉ cần dành thời gian học đều đặn và sẵn sàng thực hành.</p></article>
                </div>
                <div class="section-heading-row compact-heading"><div><span class="eyebrow coral-text">Nội dung khóa học</span><h2>Lộ trình học</h2></div></div>

                <div class="public-curriculum" data-accordion>
                    @forelse ($course->sections as $section)
                        <article class="public-section">
                            <button type="button" class="public-section-toggle" data-accordion-toggle aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                <span class="section-order">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span><strong>{{ $section->title }}</strong><small>{{ $section->lessons->count() }} bài học</small></span>
                                <i>⌄</i>
                            </button>
                            <div class="public-lessons" @if (!$loop->first) hidden @endif>
                                @forelse ($section->lessons as $lesson)
                                    <div class="public-lesson">
                                        <span class="lesson-play">{{ $lesson->type === 'video' ? '▶' : ($lesson->type === 'assignment' ? '✓' : '¶') }}</span>
                                        <div><strong>@if ($lesson->is_preview && $lesson->type === 'video')<a href="#course-preview">{{ $lesson->title }}</a>@else{{ $lesson->title }}@endif</strong><small>{{ ucfirst($lesson->type) }}@if ($lesson->duration_minutes) · {{ $lesson->duration_minutes }} phút @endif</small></div>
                                        @if ($lesson->is_preview)
                                            <span class="preview-label">Xem thử</span>
                                        @else
                                            <span class="locked-label">Khóa</span>
                                        @endif
                                    </div>
                                @empty
                                    <p class="muted-copy">Nội dung chương đang được cập nhật.</p>
                                @endforelse
                            </div>
                        </article>
                    @empty
                        <div class="empty-panel"><span>＋</span><h3>Nội dung đang được cập nhật</h3></div>
                    @endforelse
                </div>
            </div>

            <aside class="enroll-card">
                <span class="eyebrow coral-text">Quyền truy cập khóa học</span>
                <strong class="detail-price">{{ number_format($course->price, 0, ',', '.') }}đ</strong>
                <ul>
                    <li>✓ Toàn bộ bài học trong khóa</li>
                    <li>✓ Video, bài đọc và bài tập</li>
                    <li>✓ Quyền truy cập {{ $course->accessPeriodLabel() }}</li>
                </ul>
                @if (session('success'))
                    <div class="site-alert site-alert-success">{{ session('success') }}</div>
                @endif
                @auth
                    @if ($isExpired)
                        <a href="{{ route('site.checkout.show', ['course_ids' => [$course->id]]) }}" class="primary-button full-button">{{ (float) $course->price <= 0 ? 'Gia hạn miễn phí' : 'Gia hạn khóa học' }} <span>↗</span></a>
                    @elseif ($isEnrolled)
                        <a href="{{ route('site.learning.show', $course) }}" class="primary-button full-button">Tiếp tục học <span>→</span></a>
                    @else
                        <a href="{{ route('site.checkout.show', ['course_ids' => [$course->id]]) }}" class="primary-button full-button">{{ (float) $course->price <= 0 ? 'Đăng ký miễn phí' : 'Đăng ký ngay' }} <span>↗</span></a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="primary-button full-button">Đăng nhập để đăng ký <span>↗</span></a>
                @endauth
                @if ($enrollment?->expires_at)
                    <small>
                        @if ($isExpired)
                            Đã hết hạn ngày {{ $enrollment->expires_at->format('d/m/Y') }}.
                        @else
                            <strong class="remaining-time">Còn {{ $enrollment->remainingDays() }} ngày</strong><br>
                            Quyền truy cập đến {{ $enrollment->expires_at->format('d/m/Y') }}.
                        @endif
                    </small>
                @else
                    <small>Hiện tại khóa học được kích hoạt ngay sau khi đăng ký. Thanh toán sẽ được bổ sung sau.</small>
                @endif
            </aside>
        </div>
    </section>

    <section class="course-reviews-section">
        <div class="site-container reviews-layout">
            <div class="review-summary">
                <span class="eyebrow coral-text">Đánh giá khóa học</span>
                <div class="review-score">
                    <strong>{{ $course->reviews_avg_rating ? number_format($course->reviews_avg_rating, 1) : '—' }}</strong>
                    <span class="review-score-stars" aria-label="Điểm đánh giá">
                        @for ($star = 1; $star <= 5; $star++)
                            <i class="{{ $course->reviews_avg_rating >= $star ? 'is-active' : '' }}">★</i>
                        @endfor
                    </span>
                    <small>{{ $course->reviews_count }} lượt đánh giá</small>
                </div>

                @auth
                    @if ($canReview)
                        <form action="{{ route('site.course.review', $course) }}" method="POST" class="review-form">
                            @csrf
                            <h3>{{ $currentReview ? 'Cập nhật đánh giá' : 'Đánh giá của bạn' }}</h3>

                            @php($selectedRating = (int) old('rating', $currentReview?->rating ?? 0))
                            <div class="rating-stars" aria-label="Chọn số sao">
                                @for ($star = 5; $star >= 1; $star--)
                                    <input type="radio" id="rating-{{ $star }}" name="rating" value="{{ $star }}" @checked($selectedRating === $star)>
                                    <label for="rating-{{ $star }}" title="{{ $star }} sao">★</label>
                                @endfor
                            </div>

                            <textarea name="comment" rows="4" maxlength="1000" placeholder="Chia sẻ cảm nhận về khóa học (không bắt buộc)">{{ old('comment', $currentReview?->comment) }}</textarea>

                            @if ($errors->has('rating') || $errors->has('comment'))
                                <div class="review-error">{{ $errors->first('rating') ?: $errors->first('comment') }}</div>
                            @endif
                            @if (session('review_success'))
                                <div class="review-success">{{ session('review_success') }}</div>
                            @endif

                            <button type="submit" class="primary-button">{{ $currentReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }} <span>→</span></button>
                        </form>
                    @else
                        <p class="review-notice">Bạn cần đăng ký khóa học để có thể đánh giá.</p>
                    @endif
                @else
                    <p class="review-notice"><a href="{{ route('login') }}">Đăng nhập</a> và đăng ký khóa học để đánh giá.</p>
                @endauth
            </div>

            <div class="review-list">
                <h2>Nhận xét từ học viên</h2>
                @forelse ($reviews as $review)
                    <article class="review-item">
                        <div class="review-user-avatar">
                            @if ($review->user?->avatar)
                                <img src="{{ asset('storage/' . $review->user->avatar) }}" alt="{{ $review->user->name }}">
                            @else
                                <span>{{ mb_strtoupper(mb_substr($review->user?->name ?? 'U', 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <div class="review-item-heading">
                                <strong>{{ $review->user?->name ?? 'Người dùng' }}</strong>
                                <span>{{ str_repeat('★', $review->rating) }}<i>{{ str_repeat('★', 5 - $review->rating) }}</i></span>
                            </div>
                            @if ($review->comment)<p>{{ $review->comment }}</p>@endif
                            <small>{{ $review->updated_at->format('d/m/Y') }}</small>
                        </div>
                    </article>
                @empty
                    <div class="empty-panel"><span>★</span><h3>Chưa có đánh giá</h3><p>Hãy là học viên đầu tiên chia sẻ cảm nhận.</p></div>
                @endforelse
            </div>
        </div>
    </section>

    @if ($relatedCourse->isNotEmpty())
        <section class="related-section">
            <div class="site-container">
                <div class="section-heading-row"><div><span class="eyebrow coral-text">Có thể bạn quan tâm</span><h2>Khóa học liên quan</h2></div></div>
                <div class="course-grid">
                    @foreach ($relatedCourse as $courseItem)
                        @include('Site.Partials.CourseCard', ['courseItem' => $courseItem])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    <div class="mobile-enroll-bar">
        <span><small>{{ $isEnrolled ? 'Khóa học của bạn' : 'Học phí' }}</small><strong>{{ $isEnrolled ? Illuminate\Support\Str::limit($course->name, 24) : ((float) $course->price <= 0 ? 'Miễn phí' : number_format($course->price, 0, ',', '.') . 'đ') }}</strong></span>
        @auth
            <a href="{{ $isEnrolled ? route('site.learning.show', $course) : route('site.checkout.show', ['course_ids' => [$course->id]]) }}">{{ $isEnrolled ? 'Tiếp tục học' : ((float) $course->price <= 0 ? 'Đăng ký' : 'Đăng ký ngay') }} →</a>
        @else
            <a href="{{ route('login') }}">Đăng nhập →</a>
        @endauth
    </div>
@endsection
