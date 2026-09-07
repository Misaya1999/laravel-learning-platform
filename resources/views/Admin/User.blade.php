@extends('Admin.Layout.Index')

@section('content')
    <style>
    .container-fluid {
        padding: 20px 20px 0 20px !important;
        min-height: 0 !important;
    }
    .table {
        margin-bottom: 0 !important;
    }
    .table th, .table td { padding: 9px 12px !important; vertical-align: middle !important; }
    .table thead th { padding-top: 10px !important; padding-bottom: 10px !important; }
    .admin-account-row { background: #eef4fb; }
    .admin-account-row:hover { background: #e3edf8 !important; }
    .admin-account-row > th, .admin-account-row > td { border-color: #d8e4f0; }
    .account-status-stack { display: grid; gap: 5px; justify-items: start; }
    .presence-status { display: inline-flex; align-items: center; gap: 5px; color: #7b8490; font-size: 11px; font-weight: 600; }
    .presence-status::before { width: 7px; height: 7px; content: ''; border-radius: 50%; background: #aab1b8; }
    .presence-status.is-online { color: #278642; }
    .presence-status.is-online::before { background: #39b956; box-shadow: 0 0 0 3px rgba(57, 185, 86, .14); }
    .user-page-title { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }.user-page-title .page-title { margin-right: 4px; }
    .presence-summary { display: flex; gap: 6px; flex-wrap: wrap; }
    .presence-summary-item { padding: 5px 9px; display: flex; align-items: center; gap: 6px; border: 1px solid #dce3e9; background: #fff; }
    .presence-summary-item::before { width: 7px; height: 7px; content: ''; border-radius: 50%; background: #aab1b8; }
    .presence-summary-item.is-online::before { background: #39b956; box-shadow: 0 0 0 3px rgba(57,185,86,.12); }
    .presence-summary-item strong { font-size: 14px; color: #3e5569; }.presence-summary-item span { color: #7b8490; font-size: 10px; }
    .user-avatar {
        width: 34px;
        height: 34px;
        object-fit: cover;
        border-radius: 50%;
    }
    .user-avatar-fallback {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: #fff;
        background: #2962ff;
        font-weight: 700;
        font-size: 13px;
    }
    .user-courses { min-width: 190px; white-space: normal; }
    .course-access-time {
        display: block;
        margin-top: 3px;
        font-size: 12px;
    }
    .course-summary { display: flex; gap: 5px; margin-bottom: 7px; flex-wrap: wrap; }
    .course-summary .badge { padding: 5px 7px; }
    .admin-toolbar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .admin-toolbar .search-input { min-width: 280px; flex: 1; }
    .course-detail-item { padding: 12px 0; border-bottom: 1px solid #e9ecef; }
    .course-detail-item:last-child { border-bottom: 0; }
    </style>

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <div class="user-page-title">
                    <h4 class="page-title">Người dùng</h4>
                    <div class="presence-summary">
                        <div class="presence-summary-item is-online"><strong data-online-count>{{ $presenceSummary['online'] }}</strong><span>online</span></div>
                        <div class="presence-summary-item"><strong data-offline-count>{{ $presenceSummary['offline'] }}</strong><span>offline</span></div>
                    </div>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route ('Admin.Dashboard') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">User</li>
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
                    <div class="card-body">
                        <form method="GET" action="{{ route('Admin.User') }}" class="admin-toolbar">
                            <input type="search" name="search" value="{{ request('search') }}" class="form-control search-input" placeholder="Tìm tên, email hoặc số điện thoại...">
                            <select name="email_status" class="form-control" style="width:auto"><option value="">Tất cả email</option><option value="verified" @selected(request('email_status')==='verified')>Đã xác minh</option><option value="unverified" @selected(request('email_status')==='unverified')>Chưa xác minh</option></select>
                            <select name="sort" class="form-control" style="width: auto">
                                <option value="latest" @selected(request('sort', 'latest') === 'latest')>Mới nhất</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                                <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên A–Z</option>
                                <option value="name_desc" @selected(request('sort') === 'name_desc')>Tên Z–A</option>
                                <option value="courses_desc" @selected(request('sort') === 'courses_desc')>Nhiều khóa học nhất</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Search</button>
                            @if (request()->hasAny(['search', 'sort', 'email_status']))<a href="{{ route('Admin.User') }}" class="btn btn-outline-secondary">Đặt lại</a>@endif
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Avatar</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Xác minh email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Courses</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user as $users)
                                <tr @class(['admin-account-row' => $users->role === 'admin']) data-user-id="{{ $users->id }}">
                                    <th scope="row">{{ $users->id }}</th>
                                    <td>
                                        @if ($users->avatar)
                                            <img src="{{ asset('storage/' . $users->avatar) }}" alt="{{ $users->name }}" class="user-avatar">
                                        @else
                                            <span class="user-avatar-fallback">{{ mb_strtoupper(mb_substr($users->name, 0, 1)) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $users->name }}</td>
                                    <td>{{ $users->email }}</td>
                                    <td>@if($users->role === 'admin')<span class="badge badge-info">Không yêu cầu</span>@elseif($users->hasVerifiedEmail())<span class="badge badge-success">Đã xác minh</span><small class="d-block text-muted mt-1">{{ $users->email_verified_at->format('d/m/Y H:i') }}</small>@else<span class="badge badge-warning">Chưa xác minh</span>@endif</td>
                                    <td>{{ $users->phone }}</td>
                                    <td>{{ $users->role }}</td>
                                    <td>
                                        <div class="account-status-stack">
                                            <span class="badge {{ $users->status === 'active' ? 'badge-success' : ($users->status === 'blocked' ? 'badge-danger' : 'badge-secondary') }}">{{ $users->status === 'active' ? 'Hoạt động' : ($users->status === 'blocked' ? 'Đã khóa' : ucfirst($users->status)) }}</span>
                                            <span class="presence-status {{ $onlineUserIds->contains($users->id) ? 'is-online' : '' }}" data-presence-status>{{ $onlineUserIds->contains($users->id) ? 'Online' : 'Offline' }}</span>
                                        </div>
                                    </td>
                                    <td class="user-courses">
                                        @php
                                            $validCourses = $users->enrollments->filter(fn ($enrollment) => !$enrollment->isExpired())->count();
                                            $expiredCourses = $users->enrollments->filter->isExpired()->count();
                                        @endphp
                                        @if ($users->enrollments->isEmpty())
                                            <span class="text-muted">Chưa có khóa học</span>
                                        @else
                                            <div class="course-summary">
                                                <span class="badge badge-primary">{{ $users->courses_count }} tổng</span>
                                                <span class="badge badge-success">{{ $validCourses }} còn hạn</span>
                                                @if ($expiredCourses)<span class="badge badge-danger">{{ $expiredCourses }} hết hạn</span>@endif
                                            </div>
                                            <button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#userCoursesModal{{ $users->id }}">Xem khóa học</button>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($users->id !== auth()->id())
                                            <form action="{{ route('Admin.User.PasswordReset', ['user' => $users->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Gửi liên kết đặt lại mật khẩu tới {{ $users->email }}?')">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-primary btn-sm" title="Gửi liên kết đặt lại mật khẩu">Reset mật khẩu</button>
                                            </form>

                                            <form action="{{ route('Admin.User.Status', ['user' => $users->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $users->status === 'blocked' ? 'Bạn có chắc muốn mở khóa người dùng này?' : 'Bạn có chắc muốn khóa người dùng này?' }}')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn {{ $users->status === 'blocked' ? 'btn-success' : 'btn-warning' }} btn-sm">
                                                    {{ $users->status === 'blocked' ? 'Activate' : 'Block' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('Admin.User.Delete', ['user' => $users->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>   
                                        @else
                                            <span class="text-muted">Current Account</span>
                                        @endif                   
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body">
                        {{ $user->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach ($user as $users)
        @if ($users->enrollments->isNotEmpty())
            <div class="modal fade" id="userCoursesModal{{ $users->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Khóa học của {{ $users->name }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            @foreach ($users->enrollments as $enrollment)
                                @if ($enrollment->course)
                                    <div class="course-detail-item d-flex justify-content-between align-items-start">
                                        <div>
                                            <a href="{{ route('Admin.Course.Detail', $enrollment->course) }}"><strong>{{ $enrollment->course->name }}</strong></a>
                                            <small class="course-access-time {{ $enrollment->isExpired() ? 'text-danger' : 'text-muted' }}">
                                                @if ($enrollment->isExpired())
                                                    Đã hết hạn {{ $enrollment->expires_at?->format('d/m/Y') }}
                                                @elseif ($enrollment->expires_at)
                                                    Còn {{ $enrollment->remainingDays() }} ngày · đến {{ $enrollment->expires_at->format('d/m/Y') }}
                                                @else
                                                    Không giới hạn thời gian
                                                @endif
                                            </small>
                                        </div>
                                        <span class="badge {{ $enrollment->isExpired() ? 'badge-danger' : 'badge-success' }}">{{ $enrollment->isExpired() ? 'Hết hạn' : 'Còn hạn' }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button></div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
    
@endsection
