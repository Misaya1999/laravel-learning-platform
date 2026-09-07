@extends('Admin.Layout.Index')

@section('content')
    <style>
        .container-fluid { padding: 20px 20px 0 20px !important; min-height: 0 !important; }
        .table { margin-bottom: 0 !important; }
        .course-image { width: 80px; height: 50px; object-fit: cover; border-radius: 4px; }
        .course-preview-image { width: 100%; max-height: 320px; object-fit: cover; border-radius: 6px; }
        .course-name-cell { min-width: 180px; }
        .course-description-full { white-space: pre-line; line-height: 1.65; }
        .admin-toolbar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .admin-toolbar .search-input { min-width: 260px; flex: 1; }
    </style>

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center"><h4 class="page-title">Course</h4></div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('Admin.Dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Course</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body course-actions">
                        <form method="GET" action="{{ route('Admin.Course') }}" class="admin-toolbar">
                            <input type="search" name="search" value="{{ request('search') }}" class="form-control search-input" placeholder="Tìm tên, category hoặc mentor...">
                            <select name="status" class="form-control" style="width: auto">
                                <option value="">Tất cả trạng thái</option>
                                <option value="draft" @selected(request('status') === 'draft')>Bản nháp</option>
                                <option value="published" @selected(request('status') === 'published')>Đã xuất bản</option>
                                <option value="hidden" @selected(request('status') === 'hidden')>Tạm ẩn</option>
                            </select>
                            <select name="sort" class="form-control" style="width: auto">
                                <option value="latest" @selected(request('sort', 'latest') === 'latest')>Mới nhất</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                                <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A–Z</option>
                                <option value="name_desc" @selected(request('sort') === 'name_desc')>Tên Z–A</option>
                                <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá thấp đến cao</option>
                                <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá cao đến thấp</option>
                                <option value="students_desc" @selected(request('sort') === 'students_desc')>Nhiều học viên nhất</option>
                                <option value="rating_desc" @selected(request('sort') === 'rating_desc')>Rating cao nhất</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Search</button>
                            @if (request()->hasAny(['search', 'sort', 'status']))<a href="{{ route('Admin.Course') }}" class="btn btn-outline-secondary">Reset</a>@endif
                            <button type="button" class="btn btn-success ml-auto" data-toggle="modal" data-target="#addCourseModal">Add Course</button>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Category</th>
                                    <th>Mentor</th>
                                    <th>Name</th>
                                    <th>Trạng thái</th>
                                    <th>Price</th>
                                    <th>Video Duration</th>
                                    <th>Access Period</th>
                                    <th>Rating</th>
                                    <th>Accounts</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($course as $courseItem)
                                    <tr>
                                        <th scope="row">{{ $courseItem->id }}</th>
                                        <td>{{ $courseItem->category?->name ?? 'Chưa phân loại' }}</td>
                                        <td>
                                            @forelse ($courseItem->mentors as $mentorItem)
                                                <span class="badge badge-info">{{ $mentorItem->name }}</span>
                                            @empty
                                                <span class="text-muted">Chưa có mentor</span>
                                            @endforelse
                                        </td>
                                        <td class="course-name-cell">
                                            <strong>{{ $courseItem->name }}</strong><br>
                                            <button type="button" class="btn btn-outline-info btn-sm mt-2" data-toggle="modal" data-target="#courseInfoModal{{ $courseItem->id }}">Xem thông tin</button>
                                        </td>
                                        <td>
                                            <span class="badge {{ $courseItem->status === 'published' ? 'badge-success' : ($courseItem->status === 'hidden' ? 'badge-secondary' : 'badge-warning') }}">
                                                {{ $courseItem->status === 'published' ? 'Đã xuất bản' : ($courseItem->status === 'hidden' ? 'Tạm ẩn' : 'Bản nháp') }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($courseItem->price, 0, ',', '.') }} ₫</td>
                                        @php($adminDuration = (int) ($courseItem->lessons_sum_duration_minutes ?? 0))
                                        <td>{{ $adminDuration >= 60 ? intdiv($adminDuration, 60) . 'h ' . ($adminDuration % 60) . 'm' : $adminDuration . 'm' }}</td>
                                        <td><span class="badge badge-info">{{ $courseItem->accessPeriodLabel() }}</span></td>
                                        <td>
                                            @if ($courseItem->reviews_avg_rating)
                                                <span class="badge badge-warning">★ {{ number_format($courseItem->reviews_avg_rating, 1) }}</span>
                                                <small>({{ $courseItem->reviews_count }})</small>
                                            @else
                                                <span class="text-muted">Chưa có</span>
                                            @endif
                                        </td>
                                        <td><span class="badge badge-primary">{{ $courseItem->students_count }} account</span></td>
                                        <td class="text-nowrap">
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editCourseModal{{ $courseItem->id }}">Edit</button>
                                            <a href="{{ route('Admin.Course.Detail', ['course' => $courseItem->id]) }}" class="btn btn-info btn-sm">Detail</a>
                                            <form action="{{ route('Admin.Course.Delete', ['course' => $courseItem->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa khóa học này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="text-center">Chưa có khóa học.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body">{{ $course->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Course -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('Admin.Course.Store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Course</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="add-course-category">Category</label>
                            <select id="add-course-category" name="category_id" class="form-control" required>
                                <option value="">Chọn category</option>
                                @foreach ($category as $categoryItem)
                                    <option value="{{ $categoryItem->id }}" @selected(old('category_id') == $categoryItem->id)>
                                        {{ $categoryItem->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="add-course-name">Name</label>
                            <input type="text" id="add-course-name" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="add-course-status">Trạng thái</label>
                            <select id="add-course-status" name="status" class="form-control" required>
                                <option value="draft" @selected(old('status', 'draft') === 'draft')>Bản nháp</option>
                                <option value="published" @selected(old('status') === 'published')>Đã xuất bản</option>
                                <option value="hidden" @selected(old('status') === 'hidden')>Tạm ẩn</option>
                            </select>
                            <small class="text-muted">Khóa học mới nên để Bản nháp cho đến khi nội dung hoàn chỉnh.</small>
                        </div>
                        <div class="form-group">
                            <label for="add-course-mentors">Mentor</label>
                            <select id="add-course-mentors" name="mentor_ids[]" class="form-control" multiple size="{{ min(max($mentor->count(), 2), 5) }}">
                                @foreach ($mentor as $mentorItem)
                                    <option value="{{ $mentorItem->id }}" @selected(in_array($mentorItem->id, old('mentor_ids', [])))>{{ $mentorItem->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Giữ Ctrl (Windows) hoặc Command (Mac) để chọn nhiều mentor.</small>
                        </div>
                        <div class="form-group">
                            <label for="add-course-image">Image</label>
                            <input type="file" id="add-course-image" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        </div>
                        <div class="form-group">
                            <label for="add-course-description">Description</label>
                            <textarea id="add-course-description" name="description" rows="4" class="form-control">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="add-course-price">Price</label>
                            <input type="number" id="add-course-price" name="price" value="{{ old('price', 0) }}" min="0" step="1000" class="form-control" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-7">
                                <label for="add-course-access-duration">Access Duration</label>
                                <input type="number" id="add-course-access-duration" name="access_duration" value="{{ old('access_duration', 12) }}" min="1" max="3650" class="form-control" required>
                            </div>
                            <div class="form-group col-5">
                                <label for="add-course-access-unit">Unit</label>
                                <select id="add-course-access-unit" name="access_duration_unit" class="form-control" required>
                                    <option value="months" @selected(old('access_duration_unit', 'months') === 'months')>Tháng</option>
                                    <option value="days" @selected(old('access_duration_unit') === 'days')>Ngày</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Course -->
    @foreach ($course as $courseItem)
        <div class="modal fade" id="courseInfoModal{{ $courseItem->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $courseItem->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @if ($courseItem->image)
                            <img src="{{ asset('storage/' . $courseItem->image) }}" alt="{{ $courseItem->name }}" class="course-preview-image mb-4">
                        @else
                            <div class="alert alert-light text-muted text-center">Khóa học chưa có ảnh.</div>
                        @endif

                        <h6>Mô tả khóa học</h6>
                        <div class="course-description-full text-muted">{{ $courseItem->description ?: 'Khóa học chưa có mô tả.' }}</div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('Admin.Course.Detail', ['course' => $courseItem->id]) }}" class="btn btn-info">Xem nội dung khóa học</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editCourseModal{{ $courseItem->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('Admin.Course.Update', ['course' => $courseItem->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Course</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="course-category-{{ $courseItem->id }}">Category</label>
                                <select id="course-category-{{ $courseItem->id }}" name="category_id" class="form-control" required>
                                    <option value="">Chọn category</option>
                                    @foreach ($category as $categoryItem)
                                        <option value="{{ $categoryItem->id }}" @selected($courseItem->category_id == $categoryItem->id)>
                                            {{ $categoryItem->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="course-name-{{ $courseItem->id }}">Name</label>
                                <input type="text" id="course-name-{{ $courseItem->id }}" name="name" value="{{ $courseItem->name }}" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="course-status-{{ $courseItem->id }}">Trạng thái</label>
                                <select id="course-status-{{ $courseItem->id }}" name="status" class="form-control" required>
                                    <option value="draft" @selected($courseItem->status === 'draft')>Bản nháp</option>
                                    <option value="published" @selected($courseItem->status === 'published')>Đã xuất bản</option>
                                    <option value="hidden" @selected($courseItem->status === 'hidden')>Tạm ẩn</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="course-mentors-{{ $courseItem->id }}">Mentor</label>
                                <select id="course-mentors-{{ $courseItem->id }}" name="mentor_ids[]" class="form-control" multiple size="{{ min(max($mentor->count(), 2), 5) }}">
                                    @foreach ($mentor as $mentorItem)
                                        <option value="{{ $mentorItem->id }}" @selected($courseItem->mentors->contains($mentorItem->id))>{{ $mentorItem->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Có thể chọn nhiều mentor.</small>
                            </div>
                            <div class="form-group">
                                <label for="course-image-{{ $courseItem->id }}">Image</label>
                                <input type="file" id="course-image-{{ $courseItem->id }}" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted">Để trống nếu muốn giữ ảnh hiện tại.</small>
                            </div>
                            <div class="form-group">
                                <label for="course-description-{{ $courseItem->id }}">Description</label>
                                <textarea id="course-description-{{ $courseItem->id }}" name="description" rows="4" class="form-control">{{ $courseItem->description }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="course-price-{{ $courseItem->id }}">Price</label>
                                <input type="number" id="course-price-{{ $courseItem->id }}" name="price" value="{{ $courseItem->price }}" min="0" step="1000" class="form-control" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-7">
                                    <label for="course-access-duration-{{ $courseItem->id }}">Access Duration</label>
                                    <input type="number" id="course-access-duration-{{ $courseItem->id }}" name="access_duration" value="{{ $courseItem->access_duration }}" min="1" max="3650" class="form-control" required>
                                </div>
                                <div class="form-group col-5">
                                    <label for="course-access-unit-{{ $courseItem->id }}">Unit</label>
                                    <select id="course-access-unit-{{ $courseItem->id }}" name="access_duration_unit" class="form-control" required>
                                        <option value="months" @selected($courseItem->access_duration_unit === 'months')>Tháng</option>
                                        <option value="days" @selected($courseItem->access_duration_unit === 'days')>Ngày</option>
                                    </select>
                                </div>
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

@endsection
