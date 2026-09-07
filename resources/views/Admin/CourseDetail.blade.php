@extends('Admin.Layout.Index')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <style>
        .course-thumbnail {
            width: 100%;
            max-width: 260px;
            height: 160px;
            object-fit: cover;
            border-radius: 6px;
        }

        .course-thumbnail-empty {
            width: 100%;
            max-width: 260px;
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #ced4da;
            border-radius: 6px;
            background: #f8f9fa;
        }

        .course-info-item {
            margin-bottom: 18px;
        }

        .course-info-item strong {
            display: block;
            margin-bottom: 4px;
            color: #3e5569;
        }

        .course-description {
            line-height: 1.65;
        }

        .course-description span {
            display: block;
            white-space: pre-line;
        }

        .section-card {
            border-left: 4px solid #2962ff;
        }

        .lesson-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-top: 1px solid #eeeeee;
        }

        .section-toggle,
        .lesson-toggle {
            border: 0;
            background: transparent;
            padding: 0;
            color: inherit;
            cursor: pointer;
        }

        .section-arrow,
        .lesson-arrow {
            display: inline-block;
            transition: transform .2s ease;
        }

        .section-toggle[aria-expanded="true"] .section-arrow,
        .lesson-toggle[aria-expanded="true"] .lesson-arrow {
            transform: rotate(90deg);
        }

        .lesson-content {
            padding: 15px 20px;
            border-top: 1px dashed #dddddd;
            background: #fafafa;
        }

        .video-upload-progress { margin: 18px 0 0; padding: 14px; border: 1px solid #cfd8e3; border-radius: 5px; background: #f5f8fc; }
        .video-upload-progress[hidden] { display: none; }
        .video-upload-progress-heading { margin-bottom: 9px; display: flex; justify-content: space-between; gap: 15px; color: #3e5569; font-size: 12px; }
        .video-upload-progress-heading strong { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .video-upload-progress-track { height: 10px; overflow: hidden; border-radius: 8px; background: #dce4ed; }
        .video-upload-progress-track i { width: 0; height: 100%; display: block; border-radius: inherit; background: linear-gradient(90deg, #2962ff, #4e8cff); transition: width .15s linear; }
        .video-upload-progress-meta { margin-top: 8px; display: flex; justify-content: space-between; color: #73808d; font-size: 10px; }
        .video-upload-progress.is-processing .video-upload-progress-track i { width: 100% !important; background-size: 28px 28px; background-image: linear-gradient(45deg, rgba(255,255,255,.22) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.22) 50%, rgba(255,255,255,.22) 75%, transparent 75%, transparent); animation: video-progress-stripes .8s linear infinite; }
        .video-upload-progress.is-error { border-color: #e57373; background: #fff1f1; }
        .video-upload-progress.is-error .video-upload-progress-track i { background: #dc3545; }
        @keyframes video-progress-stripes { to { background-position: 28px 0; } }
    </style>

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-6 align-self-center">
                <h4 class="page-title">Chi tiết khóa học</h4>
            </div>

            <div class="col-6 text-right">
                <a
                    href="{{ route('Admin.Course') }}"
                    class="btn btn-secondary"
                >
                    Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        {{-- Thông tin Course --}}
        <div class="card">
            <div class="card-body">
                <h3 class="mb-4">{{ $course->name }}</h3>
                <div class="row">
                    <div class="col-lg-3 col-md-4 mb-3 mb-md-0">
                        @if ($course->image)
                            <img
                                src="{{ asset('storage/' . $course->image) }}"
                                alt="{{ $course->name }}"
                                class="course-thumbnail"
                            >
                        @else
                            <div class="course-thumbnail-empty text-muted">Chưa có ảnh</div>
                        @endif
                    </div>

                    <div class="col-lg-9 col-md-8">
                        <div class="row">
                            <div class="col-lg-6 course-info-item">
                                <strong>Danh mục</strong>
                                <span>{{ $course->category?->name ?? 'Chưa phân loại' }}</span>
                            </div>
                            <div class="col-lg-6 course-info-item">
                                <strong>Giảng viên</strong>
                                <span>{{ $course->mentors->pluck('name')->join(', ') ?: 'Chưa có mentor' }}</span>
                            </div>
                            <div class="col-lg-6 course-info-item">
                                <strong>Giá</strong>
                                <span>{{ number_format($course->price, 0, ',', '.') }} ₫</span>
                            </div>
                            <div class="col-lg-6 course-info-item">
                                <strong>Số chương</strong>
                                <span>{{ $course->sections->count() }}</span>
                            </div>
                            <div class="col-lg-6 course-info-item">
                                <strong>Thời hạn sử dụng</strong>
                                <span>{{ $course->accessPeriodLabel() }}</span>
                            </div>
                            <div class="col-lg-6 course-info-item">
                                <strong>Số bài học</strong>
                                <span>{{ $course->sections->sum(fn ($section) => $section->lessons->count()) }}</span>
                            </div>
                            <div class="col-12 course-info-item course-description">
                                <strong>Mô tả</strong>
                                <span>{{ $course->description ?: 'Chưa có mô tả' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Danh sách Section --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Nội dung khóa học</h4>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addSectionModal">Thêm chương</button>
        </div>

        @forelse ($course->sections as $section)
            <div class="card section-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <button type="button" class="section-toggle" data-toggle="collapse" data-target="#sectionLessons{{ $section->id }}" aria-expanded="false" aria-controls="sectionLessons{{ $section->id }}">
                            <i class="mdi mdi-chevron-right section-arrow"></i>
                            <strong>
                                Chương {{ $loop->iteration }}:
                                {{ $section->title }}
                            </strong>
                        </button>

                        <div>
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                data-toggle="modal"
                                data-target="#editSectionModal{{ $section->id }}"
                            >
                                Edit
                            </button>

                            <form
                                action="{{ route('Admin.Course.Section.Delete', ['section' => $section->id]) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Bạn có chắc muốn xóa chương này? Tất cả bài học trong chương cũng sẽ bị xóa.')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="sectionLessons{{ $section->id }}" class="collapse">
                <div class="card-body p-0">
                    @forelse ($section->lessons as $lesson)
                        <div class="lesson-row">
                            <div>
                                <button type="button" class="lesson-toggle" data-toggle="collapse" data-target="#lessonContent{{ $lesson->id }}" aria-expanded="false" aria-controls="lessonContent{{ $lesson->id }}">
                                    <i class="mdi mdi-chevron-right lesson-arrow"></i>
                                    <strong>
                                    {{ $loop->iteration }}.
                                    {{ $lesson->title }}
                                    </strong>
                                </button>

                                <span class="badge badge-info ml-2">
                                    {{ ucfirst($lesson->type) }}
                                </span>

                                @if ($lesson->type === 'video' && $lesson->duration_minutes)
                                    <small class="text-muted ml-2">
                                        {{ $lesson->duration_minutes }} phút
                                    </small>
                                @endif

                                @if ($lesson->is_preview)
                                    <span class="badge badge-success ml-2">
                                        Preview
                                    </span>
                                @endif
                            </div>

                            <div>
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    data-toggle="modal"
                                    data-target="#editLessonModal{{ $lesson->id }}"
                                >
                                    Edit
                                </button>

                                <form action="{{ route('Admin.Course.Lesson.Delete', ['lesson' => $lesson->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa bài học này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div id="lessonContent{{ $lesson->id }}" class="collapse lesson-content">
                            @if ($lesson->type === 'video')
                                @if ($lesson->video_path)
                                    <p class="mb-0"><strong>Video MP4:</strong> Đã tải lên hệ thống.</p>
                                @else
                                    <p class="mb-1"><strong>Video URL:</strong></p>
                                    <a href="{{ $lesson->video_url }}" target="_blank" rel="noopener">{{ $lesson->video_url }}</a>
                                @endif
                            @else
                                <div>{!! nl2br(e($lesson->content)) !!}</div>
                            @endif

                            @if ($lesson->attachment)
                                <div class="mt-3">
                                    <a href="{{ route('Admin.Course.Lesson.Attachment', $lesson) }}" class="btn btn-outline-secondary btn-sm">
                                        Xem file đính kèm
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted">
                            Chương này chưa có bài học.
                        </div>
                    @endforelse

                    <div class="p-3">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addLessonModal{{ $section->id }}">Add Lesson</button>
                    </div>
                </div>
            </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted">
                    Khóa học chưa có chương nào.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Add Section modal --}}
    <div class="modal fade" id="addSectionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('Admin.Course.Section.Store', ['course' => $course->id]) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Section</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="section-title">Section Title</label>
                            <input type="text" id="section-title" name="title" value="{{ old('title') }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Section modals --}}
    @foreach ($course->sections as $section)
        <div class="modal fade" id="editSectionModal{{ $section->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('Admin.Course.Section.Update', ['section' => $section->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Section</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="section-title-{{ $section->id }}">Section Title</label>
                                <input type="text" id="section-title-{{ $section->id }}" name="title" value="{{ $section->title }}" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="section-position-{{ $section->id }}">Position</label>
                                <input type="number" id="section-position-{{ $section->id }}" name="position" value="{{ $section->position }}" min="1" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Add Lesson modals --}}
    @foreach ($course->sections as $section)
        <div class="modal fade" id="addLessonModal{{ $section->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form action="{{ route('Admin.Course.Lesson.Store', ['section' => $section->id]) }}" method="POST" enctype="multipart/form-data" class="lesson-video-upload-form">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Add Lesson — {{ $section->title }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="lesson-title-{{ $section->id }}">Lesson Title</label>
                                <input type="text" id="lesson-title-{{ $section->id }}" name="title" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="lesson-type-{{ $section->id }}">Lesson Type</label>
                                <select id="lesson-type-{{ $section->id }}" name="type" class="form-control lesson-type" data-section="{{ $section->id }}" required>
                                    <option value="video">Video</option>
                                    <option value="article">Article</option>
                                    <option value="assignment">Assignment</option>
                                </select>
                            </div>

                            <div class="form-group video-field" data-section="{{ $section->id }}">
                                <label for="video-url-{{ $section->id }}">Video URL (tùy chọn)</label>
                                <input type="url" id="video-url-{{ $section->id }}" name="video_url" class="form-control video-url-input" data-duration-target="lesson-duration-{{ $section->id }}" data-status-target="lesson-duration-status-{{ $section->id }}" placeholder="https://youtube.com/watch?v=...">
                                <div class="mt-3"><label for="video-file-{{ $section->id }}">Hoặc tải video MP4 từ máy</label><input type="file" id="video-file-{{ $section->id }}" class="form-control video-file-input" accept="video/mp4,.mp4" data-duration-target="lesson-duration-{{ $section->id }}" data-status-target="lesson-duration-status-{{ $section->id }}"><small class="text-muted">Chỉ nhận MP4, tối đa 600MB. Video lớn được tự động chia thành từng phần 50MB.</small></div>
                            </div>

                            <div class="form-group content-field" data-section="{{ $section->id }}" style="display: none;">
                                <label for="lesson-content-{{ $section->id }}">Content</label>
                                <textarea id="lesson-content-{{ $section->id }}" name="content" rows="6" class="form-control" placeholder="Nội dung bài viết hoặc đề bài..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="lesson-attachment-{{ $section->id }}">Attachment</label>
                                <input type="file" id="lesson-attachment-{{ $section->id }}" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                                <small class="text-muted">PDF, Word, Excel hoặc ZIP; tối đa 10MB.</small>
                                <div class="form-check mt-2"><input type="checkbox" id="lesson-attachment-download-{{ $section->id }}" name="allow_attachment_download" value="1" class="form-check-input"><label for="lesson-attachment-download-{{ $section->id }}" class="form-check-label">Cho phép học viên tải tài liệu</label></div>
                            </div>

                            <div class="form-group video-duration-field" data-section="{{ $section->id }}">
                                <label for="lesson-duration-{{ $section->id }}">Duration (minutes)</label>
                                <input type="number" id="lesson-duration-{{ $section->id }}" name="duration_minutes" min="1" class="form-control" readonly>
                                <small id="lesson-duration-status-{{ $section->id }}" class="text-muted">Duration sẽ được lấy tự động từ video.</small>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" id="lesson-preview-{{ $section->id }}" name="is_preview" value="1" class="form-check-input">
                                <label for="lesson-preview-{{ $section->id }}" class="form-check-label">Cho phép xem thử</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Add Lesson</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Edit Lesson modals --}}
    @foreach ($course->sections as $section)
        @foreach ($section->lessons as $lesson)
            <div class="modal fade" id="editLessonModal{{ $lesson->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <form action="{{ route('Admin.Course.Lesson.Update', ['lesson' => $lesson->id]) }}" method="POST" enctype="multipart/form-data" class="lesson-video-upload-form">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Lesson — {{ $lesson->title }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="edit-lesson-title-{{ $lesson->id }}">Lesson Title</label>
                                    <input type="text" id="edit-lesson-title-{{ $lesson->id }}" name="title" value="{{ $lesson->title }}" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="edit-lesson-type-{{ $lesson->id }}">Lesson Type</label>
                                    <select id="edit-lesson-type-{{ $lesson->id }}" name="type" class="form-control edit-lesson-type" data-lesson="{{ $lesson->id }}" required>
                                        <option value="video" @selected($lesson->type === 'video')>Video</option>
                                        <option value="article" @selected($lesson->type === 'article')>Article</option>
                                        <option value="assignment" @selected($lesson->type === 'assignment')>Assignment</option>
                                    </select>
                                </div>

                                <div class="form-group edit-video-field" data-lesson="{{ $lesson->id }}">
                                    <label for="edit-video-url-{{ $lesson->id }}">Video URL (tùy chọn)</label>
                                    <input type="url" id="edit-video-url-{{ $lesson->id }}" name="video_url" value="{{ $lesson->video_url }}" class="form-control video-url-input" data-duration-target="edit-lesson-duration-{{ $lesson->id }}" data-status-target="edit-lesson-duration-status-{{ $lesson->id }}" placeholder="https://youtube.com/watch?v=...">
                                    <div class="mt-3"><label for="edit-video-file-{{ $lesson->id }}">Hoặc tải video MP4 mới</label><input type="file" id="edit-video-file-{{ $lesson->id }}" class="form-control video-file-input" accept="video/mp4,.mp4" data-duration-target="edit-lesson-duration-{{ $lesson->id }}" data-status-target="edit-lesson-duration-status-{{ $lesson->id }}">@if($lesson->video_path)<small class="d-block mt-1 text-success">Đang sử dụng video MP4 đã tải lên. Để trống nếu muốn giữ nguyên.</small>@else<small class="text-muted">Chỉ nhận MP4, tối đa 600MB; upload theo từng phần 50MB.</small>@endif</div>
                                </div>

                                <div class="form-group edit-content-field" data-lesson="{{ $lesson->id }}">
                                    <label for="edit-lesson-content-{{ $lesson->id }}">Content</label>
                                    <textarea id="edit-lesson-content-{{ $lesson->id }}" name="content" rows="6" class="form-control" placeholder="Nội dung bài viết hoặc đề bài...">{{ $lesson->content }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label for="edit-lesson-attachment-{{ $lesson->id }}">Attachment</label>
                                    <input type="file" id="edit-lesson-attachment-{{ $lesson->id }}" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                                    @if ($lesson->attachment)
                                        <small class="d-block mt-1">
                                            File hiện tại:
                                            <a href="{{ route('Admin.Course.Lesson.Attachment', $lesson) }}">Tải file</a>
                                        </small>
                                    @endif
                                    <small class="text-muted">Để trống nếu muốn giữ file hiện tại.</small>
                                    <div class="form-check mt-2"><input type="checkbox" id="edit-lesson-attachment-download-{{ $lesson->id }}" name="allow_attachment_download" value="1" class="form-check-input" @checked($lesson->allow_attachment_download)><label for="edit-lesson-attachment-download-{{ $lesson->id }}" class="form-check-label">Cho phép học viên tải tài liệu</label></div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6 edit-video-duration-field" data-lesson="{{ $lesson->id }}">
                                        <label for="edit-lesson-duration-{{ $lesson->id }}">Duration (minutes)</label>
                                        <input type="number" id="edit-lesson-duration-{{ $lesson->id }}" name="duration_minutes" value="{{ $lesson->duration_minutes }}" min="1" class="form-control" readonly>
                                        <small id="edit-lesson-duration-status-{{ $lesson->id }}" class="text-muted">Duration sẽ được cập nhật khi URL thay đổi.</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="edit-lesson-position-{{ $lesson->id }}">Position</label>
                                        <input type="number" id="edit-lesson-position-{{ $lesson->id }}" name="position" value="{{ $lesson->position }}" min="1" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" id="edit-lesson-preview-{{ $lesson->id }}" name="is_preview" value="1" class="form-check-input" @checked($lesson->is_preview)>
                                    <label for="edit-lesson-preview-{{ $lesson->id }}" class="form-check-label">Cho phép xem thử</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
                </div>
            </div>
        @endforeach
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.lesson-type').forEach(function (select) {
                function updateLessonFields() {
                    const sectionId = select.dataset.section;
                    const videoField = document.querySelector('.video-field[data-section="' + sectionId + '"]');
                    const contentField = document.querySelector('.content-field[data-section="' + sectionId + '"]');
                    const durationField = document.querySelector('.video-duration-field[data-section="' + sectionId + '"]');
                    const videoInput = videoField.querySelector('input');
                    const contentInput = contentField.querySelector('textarea');
                    const isVideo = select.value === 'video';

                    videoField.style.display = isVideo ? 'block' : 'none';
                    durationField.style.display = isVideo ? 'block' : 'none';
                    contentField.style.display = isVideo ? 'none' : 'block';
                    videoInput.required = false;
                    contentInput.required = !isVideo;

                    if (!isVideo) {
                        durationField.querySelector('input').value = '';
                    }
                }

                select.addEventListener('change', updateLessonFields);
                updateLessonFields();
            });

            document.querySelectorAll('.edit-lesson-type').forEach(function (select) {
                function updateEditLessonFields() {
                    const lessonId = select.dataset.lesson;
                    const videoField = document.querySelector('.edit-video-field[data-lesson="' + lessonId + '"]');
                    const contentField = document.querySelector('.edit-content-field[data-lesson="' + lessonId + '"]');
                    const durationField = document.querySelector('.edit-video-duration-field[data-lesson="' + lessonId + '"]');
                    const videoInput = videoField.querySelector('input');
                    const contentInput = contentField.querySelector('textarea');
                    const isVideo = select.value === 'video';

                    videoField.style.display = isVideo ? 'block' : 'none';
                    durationField.style.display = isVideo ? 'block' : 'none';
                    contentField.style.display = isVideo ? 'none' : 'block';
                    videoInput.required = false;
                    contentInput.required = !isVideo;

                    if (!isVideo) {
                        durationField.querySelector('input').value = '';
                    }
                }

                select.addEventListener('change', updateEditLessonFields);
                updateEditLessonFields();
            });

            let youtubeApiPromise;

            function getYoutubeId(url) {
                const match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/);
                return match ? match[1] : null;
            }

            function loadYoutubeApi() {
                if (window.YT && window.YT.Player) {
                    return Promise.resolve();
                }

                if (youtubeApiPromise) {
                    return youtubeApiPromise;
                }

                youtubeApiPromise = new Promise(function (resolve) {
                    const previousCallback = window.onYouTubeIframeAPIReady;

                    window.onYouTubeIframeAPIReady = function () {
                        if (typeof previousCallback === 'function') {
                            previousCallback();
                        }
                        resolve();
                    };

                    if (!document.querySelector('script[src="https://www.youtube.com/iframe_api"]')) {
                        const script = document.createElement('script');
                        script.src = 'https://www.youtube.com/iframe_api';
                        document.head.appendChild(script);
                    }
                });

                return youtubeApiPromise;
            }

            function getYoutubeDuration(videoId) {
                return loadYoutubeApi().then(function () {
                    return new Promise(function (resolve, reject) {
                        const wrapper = document.createElement('div');
                        const playerElement = document.createElement('div');
                        const playerId = 'youtube-duration-' + Date.now() + '-' + Math.random().toString(36).slice(2);
                        let attempts = 0;

                        wrapper.style.position = 'fixed';
                        wrapper.style.left = '-9999px';
                        playerElement.id = playerId;
                        wrapper.appendChild(playerElement);
                        document.body.appendChild(wrapper);

                        const player = new YT.Player(playerId, {
                            width: 1,
                            height: 1,
                            videoId: videoId,
                            events: {
                                onReady: function () {
                                    const timer = window.setInterval(function () {
                                        const duration = player.getDuration();
                                        attempts++;

                                        if (duration > 0) {
                                            window.clearInterval(timer);
                                            player.destroy();
                                            wrapper.remove();
                                            resolve(duration);
                                        } else if (attempts >= 20) {
                                            window.clearInterval(timer);
                                            player.destroy();
                                            wrapper.remove();
                                            reject(new Error('Không lấy được duration từ YouTube.'));
                                        }
                                    }, 250);
                                },
                                onError: function () {
                                    player.destroy();
                                    wrapper.remove();
                                    reject(new Error('Video YouTube không hợp lệ hoặc không công khai.'));
                                }
                            }
                        });
                    });
                });
            }

            function getDirectVideoDuration(url) {
                return new Promise(function (resolve, reject) {
                    const video = document.createElement('video');
                    const timeout = window.setTimeout(function () {
                        video.removeAttribute('src');
                        reject(new Error('Quá thời gian đọc thông tin video.'));
                    }, 15000);

                    video.preload = 'metadata';
                    video.onloadedmetadata = function () {
                        window.clearTimeout(timeout);
                        const duration = video.duration;
                        video.removeAttribute('src');

                        if (Number.isFinite(duration) && duration > 0) {
                            resolve(duration);
                        } else {
                            reject(new Error('Video không có duration hợp lệ.'));
                        }
                    };
                    video.onerror = function () {
                        window.clearTimeout(timeout);
                        reject(new Error('Không đọc được link video trực tiếp.'));
                    };
                    video.src = url;
                });
            }

            document.querySelectorAll('.video-url-input').forEach(function (input) {
                input.addEventListener('change', async function () {
                    const durationInput = document.getElementById(input.dataset.durationTarget);
                    const status = document.getElementById(input.dataset.statusTarget);
                    const url = input.value.trim();

                    if (!url) {
                        durationInput.value = '';
                        status.textContent = 'Duration sẽ được lấy tự động từ video.';
                        return;
                    }

                    durationInput.value = '';
                    status.textContent = 'Đang lấy duration...';

                    try {
                        const youtubeId = getYoutubeId(url);
                        const seconds = youtubeId
                            ? await getYoutubeDuration(youtubeId)
                            : await getDirectVideoDuration(url);
                        const minutes = Math.max(1, Math.ceil(seconds / 60));

                        durationInput.value = minutes;
                        status.textContent = 'Duration: ' + minutes + ' phút (' + Math.round(seconds) + ' giây).';
                    } catch (error) {
                        status.textContent = error.message + ' Hỗ trợ YouTube hoặc link file video trực tiếp.';
                    }
                });
            });

            document.querySelectorAll('.video-file-input').forEach(function (input) {
                input.addEventListener('change', function () {
                    const file = input.files && input.files[0];
                    const durationInput = document.getElementById(input.dataset.durationTarget);
                    const status = document.getElementById(input.dataset.statusTarget);
                    if (!file) return;

                    const videoField = input.closest('.video-field, .edit-video-field');
                    const urlInput = videoField && videoField.querySelector('.video-url-input');
                    if (urlInput) urlInput.value = '';
                    const objectUrl = URL.createObjectURL(file);
                    const video = document.createElement('video');
                    status.textContent = 'Đang đọc thời lượng file MP4...';
                    video.preload = 'metadata';
                    video.onloadedmetadata = function () {
                        const seconds = video.duration;
                        URL.revokeObjectURL(objectUrl);
                        if (Number.isFinite(seconds) && seconds > 0) {
                            const minutes = Math.max(1, Math.ceil(seconds / 60));
                            durationInput.value = minutes;
                            status.textContent = 'Duration: ' + minutes + ' phút (' + Math.round(seconds) + ' giây).';
                        } else {
                            status.textContent = 'Không đọc được thời lượng. Bạn có thể nhập duration thủ công.';
                            durationInput.readOnly = false;
                        }
                    };
                    video.onerror = function () {
                        URL.revokeObjectURL(objectUrl);
                        status.textContent = 'File MP4 không hợp lệ hoặc trình duyệt không đọc được metadata.';
                        durationInput.readOnly = false;
                    };
                    video.src = objectUrl;
                });
            });

        });
    </script>

    <div id="lesson-video-upload-config"
         data-chunk-url="{{ route('Admin.Course.Lesson.Video.Chunk') }}"
         data-complete-url="{{ route('Admin.Course.Lesson.Video.Complete') }}"
         hidden></div>

    {{-- Nhúng trực tiếp để trình chặn nội dung hoặc proxy không thể chặn riêng file upload JS. --}}
    <script>
        {!! file_get_contents(public_path('js/lesson-video-upload.js')) !!}
    </script>
@endsection
