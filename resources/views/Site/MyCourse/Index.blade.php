@extends('Site.Layout.Index')

@section('title', 'Khóa học của tôi — KhoaHocPlus')
@section('description', 'Danh sách các khóa học bạn đã đăng ký tại KhoaHocPlus.')

@section('content')
    <section class="page-hero my-course-page-hero">
        <div class="site-container page-hero-grid">
            <div>
                <span class="eyebrow"><i></i> Không gian học tập</span>
                <h1>Khóa học<br><em>của tôi.</em></h1>
            </div>
            <div class="course-count-block">
                <h2>Thông tin các khóa học</h2>
                <div class="course-count-summary">
                    <div class="page-hero-note">
                        <strong>{{ $course->total() }}</strong>
                        <span>đã đăng ký</span>
                    </div>
                    <div class="page-hero-note">
                        <strong>{{ $activeCourseCount }}</strong>
                        <span>còn hạn</span>
                    </div>
                    <div class="page-hero-note">
                        <strong>{{ $expiredCourseCount }}</strong>
                        <span>đã hết hạn</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="my-course-page-section">
        <div class="site-container">
            @if (session('success'))
                <div class="site-alert site-alert-success">{{ session('success') }}</div>
            @endif
            @if (session('info'))
                <div class="site-alert site-alert-info">{{ session('info') }}</div>
            @endif

            @if ($course->isNotEmpty())
                <section class="personal-learning-overview my-course-overview" aria-labelledby="learning-overview-title">
                    <div class="learning-progress-ring" style="--progress: {{ $overallProgress }}">
                        <span><strong>{{ $overallProgress }}%</strong><small>toàn bộ</small></span>
                    </div>
                    <div class="personal-learning-copy">
                        <span class="eyebrow coral-text">Tiến độ học tập</span>
                        <h2 id="learning-overview-title">Bạn đã hoàn thành {{ $completedLessonCount }}/{{ $totalLessonCount }} bài học.</h2>
                        @if ($recentLessonView)
                            <p>Bài vừa xem: <strong>{{ $recentLessonView->lesson->title }}</strong> trong khóa {{ $recentLessonView->lesson->section->course->name }}.</p>
                        @else
                            <p>Chọn một khóa học bên dưới để bắt đầu hành trình hôm nay.</p>
                        @endif
                    </div>
                    @if ($recentLessonView)
                        <a href="{{ route('site.learning.show', ['course' => $recentLessonView->lesson->section->course, 'lesson' => $recentLessonView->lesson]) }}" class="primary-button learning-continue-button">Học tiếp bài vừa xem <span>→</span></a>
                    @endif
                </section>

                <div class="learning-course-grid">
                    @foreach ($course as $courseItem)
                        @php
                            $expiresAt = $courseItem->pivot->expires_at
                                ? Illuminate\Support\Carbon::parse($courseItem->pivot->expires_at)
                                : null;
                            $isCourseExpired = $expiresAt?->isPast() ?? false;
                            $remainingDays = $expiresAt
                                ? max(0, (int) ceil(($expiresAt->getTimestamp() - now()->getTimestamp()) / 86400))
                                : null;
                        @endphp
                        <article class="learning-course-card">
                            <a href="{{ route('site.course.show', $courseItem) }}" class="learning-course-image">
                                @if ($courseItem->image)
                                    <img src="{{ asset('storage/' . $courseItem->image) }}" alt="{{ $courseItem->name }}">
                                @else
                                    <span>{{ str_pad((string) $courseItem->id, 2, '0', STR_PAD_LEFT) }}</span>
                                    <strong>LEARN<br>TO DO.</strong>
                                @endif
                            </a>
                            <div class="learning-course-body">
                                <div class="learning-course-meta">
                                    <span>{{ $courseItem->category?->name ?? 'Khóa học' }}</span>
                                    <span class="enrollment-status {{ $isCourseExpired ? 'enrollment-status-expired' : 'enrollment-status-' . $courseItem->pivot->status }}">
                                        {{ $isCourseExpired ? 'Hết hạn' : ($courseItem->pivot->status === 'completed' ? 'Đã hoàn thành' : 'Đang học') }}
                                    </span>
                                </div>
                                <h2>{{ $courseItem->name }}</h2>
                                <p>{{ $courseItem->lessons_count }} bài học
                                    @if (($courseItem->lessons_sum_duration_minutes ?? 0) > 0)
                                        · {{ $courseItem->lessons_sum_duration_minutes }} phút
                                    @endif
                                </p>
                                <div class="my-course-progress" aria-label="Đã hoàn thành {{ $courseItem->progress_percentage }} phần trăm khóa học">
                                    <div><strong>Tiến độ {{ $courseItem->progress_percentage }}%</strong><span>{{ $courseItem->completed_lessons_count }}/{{ $courseItem->lessons_count }} bài hoàn thành</span></div>
                                    <div class="course-progress-track"><i style="width: {{ $courseItem->progress_percentage }}%"></i></div>
                                    @if ($courseItem->recent_lesson_view)
                                        <small>Bài vừa xem: {{ $courseItem->recent_lesson_view->lesson->title }}</small>
                                    @endif
                                </div>
                                <div class="learning-course-footer">
                                    <small>
                                        @if ($expiresAt)
                                            @if ($isCourseExpired)
                                                Đã hết hạn {{ $expiresAt->format('d/m/Y') }}
                                            @else
                                                <strong class="remaining-time">Còn {{ $remainingDays }} ngày</strong> · đến {{ $expiresAt->format('d/m/Y') }}
                                            @endif
                                        @else
                                            Đăng ký {{ Illuminate\Support\Carbon::parse($courseItem->pivot->enrolled_at)->format('d/m/Y') }}
                                        @endif
                                    </small>
                                    @if ($isCourseExpired)
                                        <a href="{{ route('site.checkout.show', ['course_ids' => [$courseItem->id]]) }}" class="primary-button">Gia hạn khóa học <span>↗</span></a>
                                    @else
                                        <a href="{{ route('site.learning.show', ['course' => $courseItem, 'lesson' => $courseItem->recent_lesson_view?->lesson]) }}" class="primary-button">{{ $courseItem->recent_lesson_view ? 'Học tiếp' : 'Bắt đầu học' }} <span>→</span></a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="site-pagination">{{ $course->links() }}</div>
            @else
                <div class="empty-panel my-course-page-empty">
                    <span>＋</span>
                    <h2>Bạn chưa đăng ký khóa học nào</h2>
                    <p>Khám phá các chủ đề và chọn khóa học phù hợp để bắt đầu.</p>
                    <a href="{{ route('site.course.index') }}" class="primary-button">Khám phá khóa học <span>↗</span></a>
                </div>
            @endif
        </div>
    </section>
@endsection
