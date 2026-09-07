@extends('Site.Layout.Index')

@section('title', $lesson->title . ' — ' . $course->name)

@section('content')
<section class="learning-page">
    <aside class="learning-sidebar">
        <a href="{{ route('site.my-courses.index') }}" class="learning-back">← Khóa học của tôi</a>
        <h2>{{ $course->name }}</h2>
        <div class="course-progress-box">
            <div><strong>{{ $progressPercentage }}%</strong><span>{{ $completedCount }}/{{ $course->lessons->count() }} bài hoàn thành</span></div>
            <div class="course-progress-track"><i style="width: {{ $progressPercentage }}%"></i></div>
        </div>
        <div class="learning-curriculum">
            @foreach ($course->sections as $section)
                <div class="learning-section">
                    <strong><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $section->title }}</strong>
                    @foreach ($section->lessons as $sectionLesson)
                        <a href="{{ route('site.learning.show', [$course, $sectionLesson]) }}" class="{{ $sectionLesson->is($lesson) ? 'active' : '' }}">
                            <i class="{{ $completedLessonIds->contains($sectionLesson->id) ? 'completed' : '' }}">{{ $completedLessonIds->contains($sectionLesson->id) ? '✓' : ($sectionLesson->type === 'video' ? '▶' : ($sectionLesson->type === 'assignment' ? '!' : '¶')) }}</i>
                            <span>{{ $sectionLesson->title }}<small>{{ $sectionLesson->duration_minutes ? $sectionLesson->duration_minutes . ' phút' : ($sectionLesson->type === 'article' ? 'Bài viết' : 'Bài tập') }}</small></span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
    </aside>

    <main class="learning-main">
        <div class="learning-heading">
            <span class="eyebrow coral-text">{{ $lesson->type === 'video' ? 'Video' : ($lesson->type === 'article' ? 'Bài viết' : 'Bài tập') }}</span>
            <h1>{{ $lesson->title }}</h1>
            <p>{{ $lesson->section->title }}</p>
        </div>

        <article class="lesson-viewer">
            @if ($lesson->type === 'video')
                @if ($lesson->video_path)
                    <video class="lesson-direct-video" controls controlsList="nodownload" preload="metadata"><source src="{{ route('site.learning.video', [$course, $lesson]) }}" type="video/mp4">Trình duyệt của bạn không hỗ trợ video MP4.</video>
                @elseif ($lesson->videoEmbedUrl())
                    <div class="lesson-video"><iframe src="{{ $lesson->videoEmbedUrl() }}" title="{{ $lesson->title }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
                @elseif (preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $lesson->video_url ?? ''))
                    <video class="lesson-direct-video" controls controlsList="nodownload"><source src="{{ $lesson->video_url }}"></video>
                @else
                    <div class="lesson-external"><p>Video được lưu trên một nền tảng bên ngoài.</p><a href="{{ $lesson->video_url }}" target="_blank" rel="noopener" class="primary-button">Mở video <span>↗</span></a></div>
                @endif
            @else
                <div class="lesson-article-content">{!! nl2br(e($lesson->content)) !!}</div>
            @endif

            @if ($lesson->attachment)
                @php($attachmentExtension = strtolower(pathinfo($lesson->attachment, PATHINFO_EXTENSION)))
                @if ($attachmentExtension === 'pdf')
                    <section class="lesson-document-viewer" data-pdf-viewer data-pdf-url="{{ $attachmentUrl }}" data-watermark="{{ auth()->user()->name }} · {{ auth()->user()->email }} · KhoaHocPlus">
                        <div class="lesson-document-heading">
                            <div><strong>Tài liệu bài học</strong><small>Đọc trực tiếp tại đây, chỉ dành cho học viên của khóa học.</small></div>
                            <div class="lesson-document-actions">
                                <button type="button" class="light-button" data-pdf-fullscreen>Toàn màn hình ⛶</button>
                                @if ($attachmentDownloadUrl)<a href="{{ $attachmentDownloadUrl }}" class="light-button">Tải xuống ↓</a>@endif
                            </div>
                        </div>
                        <div class="lesson-pdf-toolbar" aria-label="Điều khiển tài liệu">
                            <span><strong data-pdf-pages>—</strong> trang</span>
                            <i></i>
                            <button type="button" data-pdf-zoom-out aria-label="Thu nhỏ">−</button>
                            <span data-pdf-zoom>100%</span>
                            <button type="button" data-pdf-zoom-in aria-label="Phóng to">＋</button>
                        </div>
                        <div class="lesson-pdf-stage"><div data-pdf-pages-container aria-label="Tài liệu {{ $lesson->title }}"></div><p data-pdf-status>Đang mở tài liệu...</p></div>
                    </section>
                @else
                    <div class="lesson-attachment">
                        <div><strong>Tài liệu đính kèm</strong><small>Định dạng {{ strtoupper($attachmentExtension) }} {{ $attachmentDownloadUrl ? 'được giảng viên cho phép tải xuống.' : 'không được phép tải xuống.' }}</small></div>
                        @if ($attachmentDownloadUrl)<a href="{{ $attachmentDownloadUrl }}" class="light-button">Tải tài liệu ↓</a>@endif
                    </div>
                @endif
            @endif
        </article>

        @if (session('progress_success'))<div class="progress-message">{{ session('progress_success') }}</div>@endif
        <form action="{{ route('site.learning.progress', [$course, $lesson]) }}" method="POST" class="lesson-complete-form">
            @csrf
            <input type="hidden" name="completed" value="{{ $isLessonCompleted ? 0 : 1 }}">
            <button type="submit" class="lesson-complete-button {{ $isLessonCompleted ? 'completed' : '' }}"><span>{{ $isLessonCompleted ? '✓' : '○' }}</span>{{ $isLessonCompleted ? 'Bài học đã hoàn thành' : 'Đánh dấu đã hoàn thành' }}</button>
        </form>

        <nav class="lesson-navigation">
            <div>@if ($previousLesson)<a href="{{ route('site.learning.show', [$course, $previousLesson]) }}" class="light-button">← Bài trước</a>@endif</div>
            <div>@if ($nextLesson)<a href="{{ route('site.learning.show', [$course, $nextLesson]) }}" class="primary-button">Bài tiếp theo →</a>@else<span class="lesson-finished">Bạn đã đến bài cuối cùng.</span>@endif</div>
        </nav>
    </main>
</section>
@endsection

@if ($lesson->attachment && strtolower(pathinfo($lesson->attachment, PATHINFO_EXTENSION)) === 'pdf')
    @push('scripts')
        @vite('resources/js/pdf-viewer.js')
    @endpush
@endif
