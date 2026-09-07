@extends('Admin.Layout.Index')

@section('content')
    <style>
        .container-fluid { padding: 20px 20px 0 !important; min-height: 0 !important; }
        .mentor-avatar { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; }
        .mentor-avatar-fallback { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; color: #fff; background: #2962ff; font-size: 13px; font-weight: 700; }
        .mentor-bio { max-width: 360px; white-space: normal; }
        .admin-toolbar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .admin-toolbar .search-input { min-width: 260px; flex: 1; }
    </style>

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center"><h4 class="page-title">Mentor</h4></div>
            <div class="col-7 align-self-center text-right"><a href="{{ route('Admin.Dashboard') }}">Home</a> / Mentor</div>
        </div>
    </div>

    <div class="container-fluid">
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('Admin.Mentor') }}" class="admin-toolbar">
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control search-input" placeholder="Tìm tên, email hoặc giới thiệu...">
                    <select name="sort" class="form-control" style="width: auto">
                        <option value="latest" @selected(request('sort', 'latest') === 'latest')>Mới nhất</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                        <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A–Z</option>
                        <option value="name_desc" @selected(request('sort') === 'name_desc')>Tên Z–A</option>
                        <option value="courses_desc" @selected(request('sort') === 'courses_desc')>Nhiều khóa học nhất</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Search</button>
                    @if (request()->hasAny(['search', 'sort']))<a href="{{ route('Admin.Mentor') }}" class="btn btn-outline-secondary">Reset</a>@endif
                    <button type="button" class="btn btn-success ml-auto" data-toggle="modal" data-target="#addMentorModal">Add Mentor</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>ID</th><th>Avatar</th><th>Mentor</th><th>Email</th><th>Chuyên môn</th><th>Giới thiệu</th><th>Khóa học</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse ($mentor as $mentorItem)
                            <tr>
                                <td>{{ $mentorItem->id }}</td>
                                <td>
                                    @if ($mentorItem->avatar)
                                        <img src="{{ asset('storage/' . $mentorItem->avatar) }}" alt="{{ $mentorItem->name }}" class="mentor-avatar">
                                    @else
                                        <span class="mentor-avatar-fallback">{{ mb_strtoupper(mb_substr($mentorItem->name, 0, 1)) }}</span>
                                    @endif
                                </td>
                                <td>{{ $mentorItem->name }} @if($mentorItem->is_main)<span class="badge badge-primary ml-1">Chính</span>@endif</td>
                                <td>{{ $mentorItem->email ?: '—' }}</td>
                                <td>{{ $mentorItem->specialty ?: '—' }}</td>
                                <td class="mentor-bio">{{ Illuminate\Support\Str::limit($mentorItem->bio, 100) }}</td>
                                <td>{{ $mentorItem->courses_count }}</td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editMentorModal{{ $mentorItem->id }}">Edit</button>
                                    <form action="{{ route('Admin.Mentor.Delete', $mentorItem) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa mentor này?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center">Chưa có mentor.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">{{ $mentor->links() }}</div>
        </div>
    </div>

    <div class="modal fade" id="addMentorModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <form action="{{ route('Admin.Mentor.Store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Add Mentor</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body">
                    <div class="form-group"><label>Name</label><input type="text" name="name" value="{{ old('name') }}" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" class="form-control"></div>
                    <div class="form-group"><label>Chuyên môn</label><input type="text" name="specialty" value="{{ old('specialty') }}" class="form-control" placeholder="Ví dụ: Kinh tế vĩ mô, Phân tích dữ liệu..."></div>
                    <div class="form-group"><label>Avatar</label><input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp"></div>
                    <div class="form-group"><label>Giới thiệu</label><textarea name="bio" rows="3" class="form-control">{{ old('bio') }}</textarea></div>
                    <div class="form-group"><label>Triết lý giảng dạy</label><textarea name="philosophy" rows="3" class="form-control">{{ old('philosophy') }}</textarea></div>
                    <div class="form-group"><label>Ảnh bằng cấp, chứng chỉ</label><input type="file" name="certificates[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple><small class="text-muted">Có thể chọn tối đa 10 ảnh, mỗi ảnh không quá 4 MB.</small></div>
                    <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="add-main-mentor" name="is_main" value="1" @checked(old('is_main'))><label class="custom-control-label" for="add-main-mentor">Đặt làm mentor chính</label></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Add Mentor</button></div>
            </form>
        </div></div>
    </div>

    @foreach ($mentor as $mentorItem)
        <div class="modal fade" id="editMentorModal{{ $mentorItem->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document"><div class="modal-content">
                <form action="{{ route('Admin.Mentor.Update', $mentorItem) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="modal-header"><h5 class="modal-title">Edit Mentor</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                    <div class="modal-body">
                        <div class="form-group"><label>Name</label><input type="text" name="name" value="{{ $mentorItem->name }}" class="form-control" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ $mentorItem->email }}" class="form-control"></div>
                        <div class="form-group"><label>Chuyên môn</label><input type="text" name="specialty" value="{{ $mentorItem->specialty }}" class="form-control"></div>
                        <div class="form-group"><label>Avatar</label><input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp"><small class="text-muted">Để trống để giữ ảnh hiện tại.</small></div>
                        <div class="form-group"><label>Giới thiệu</label><textarea name="bio" rows="3" class="form-control">{{ $mentorItem->bio }}</textarea></div>
                        <div class="form-group"><label>Triết lý giảng dạy</label><textarea name="philosophy" rows="3" class="form-control">{{ $mentorItem->philosophy }}</textarea></div>
                        <div class="form-group"><label>Thêm ảnh bằng cấp, chứng chỉ</label><input type="file" name="certificates[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple><small class="text-muted">Ảnh mới sẽ được thêm vào danh sách hiện tại.</small></div>
                        @if($mentorItem->certificates->isNotEmpty())
                            <div class="form-group"><label>Ảnh hiện tại</label><div class="d-flex flex-wrap" style="gap:10px">@foreach($mentorItem->certificates as $certificate)<div class="position-relative"><img src="{{ asset('storage/' . $certificate->image) }}" alt="Chứng chỉ" style="width:90px;height:65px;object-fit:cover;border-radius:4px"><button type="submit" form="delete-certificate-{{ $certificate->id }}" class="btn btn-danger btn-sm position-absolute" style="right:3px;top:3px;padding:1px 6px" title="Xóa ảnh">×</button></div>@endforeach</div></div>
                        @endif
                        <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="main-mentor-{{ $mentorItem->id }}" name="is_main" value="1" @checked($mentorItem->is_main)><label class="custom-control-label" for="main-mentor-{{ $mentorItem->id }}">Đặt làm mentor chính</label></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
                </form>
            </div></div>
        </div>
    @endforeach
    @foreach($mentor as $mentorItem)
        @foreach($mentorItem->certificates as $certificate)
            <form id="delete-certificate-{{ $certificate->id }}" action="{{ route('Admin.Mentor.Certificate.Delete', $certificate) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
        @endforeach
    @endforeach
@endsection
