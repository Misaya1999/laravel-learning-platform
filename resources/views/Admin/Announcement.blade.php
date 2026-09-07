@extends('Admin.Layout.Index')

@section('content')
<div class="page-breadcrumb"><div class="row"><div class="col-6 align-self-center"><h4 class="page-title">Trung tâm thông báo</h4></div><div class="col-6 text-right"><button class="btn btn-primary" data-toggle="modal" data-target="#addAnnouncementModal">+ Thêm thông báo</button></div></div></div>
<div class="container-fluid">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="card"><div class="card-body">
        <form method="GET" class="form-row align-items-end mb-4">
            <div class="col-md-6"><label>Tìm kiếm</label><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Tiêu đề hoặc nội dung"></div>
            <div class="col-md-3"><label>Trạng thái</label><select class="form-control" name="status"><option value="">Tất cả</option><option value="published" @selected(request('status') === 'published')>Đã đăng</option><option value="draft" @selected(request('status') === 'draft')>Bản nháp</option></select></div>
            <div class="col-md-3"><button class="btn btn-dark">Lọc</button> <a href="{{ route('Admin.Announcement') }}" class="btn btn-light">Đặt lại</a></div>
        </form>
        <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Thông báo</th><th>Loại</th><th>Trạng thái</th><th>Thời gian đăng</th><th>Thao tác</th></tr></thead><tbody>
        @forelse($announcements as $announcement)
            <tr><td style="min-width:280px"><strong>{{ $announcement->title }}</strong><div class="text-muted small mt-1">{{ Illuminate\Support\Str::limit($announcement->content, 110) }}</div></td><td><span class="badge badge-{{ $announcement->type === 'warning' ? 'warning' : ($announcement->type === 'success' ? 'success' : 'info') }}">{{ ['info'=>'Thông tin','success'=>'Thành công','warning'=>'Quan trọng'][$announcement->type] }}</span></td><td><span class="badge badge-{{ $announcement->is_published ? 'success' : 'secondary' }}">{{ $announcement->is_published ? 'Đã đăng' : 'Bản nháp' }}</span></td><td>{{ $announcement->published_at?->timezone(config('app.display_timezone'))->format('d/m/Y H:i') ?? '—' }}</td><td class="text-nowrap"><button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editAnnouncement{{ $announcement->id }}">Sửa</button> <form class="d-inline" method="POST" action="{{ route('Admin.Announcement.Delete', $announcement) }}" onsubmit="return confirm('Xóa thông báo này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Xóa</button></form></td></tr>
        @empty<tr><td colspan="5" class="text-center py-5">Chưa có thông báo.</td></tr>@endforelse
        </tbody></table></div>
        <div class="mt-3">{{ $announcements->links() }}</div>
    </div></div>
</div>

<div class="modal fade" id="addAnnouncementModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" action="{{ route('Admin.Announcement.Store') }}">@csrf
    <div class="modal-header"><h5 class="modal-title">Thêm thông báo</h5><button class="close" data-dismiss="modal">×</button></div>
    <div class="modal-body">@include('Admin.Partials.AnnouncementForm', ['announcement' => null])</div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Hủy</button><button class="btn btn-primary">Lưu thông báo</button></div>
</form></div></div></div>

@foreach($announcements as $announcement)
<div class="modal fade" id="editAnnouncement{{ $announcement->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" action="{{ route('Admin.Announcement.Update', $announcement) }}">@csrf @method('PUT')
    <div class="modal-header"><h5 class="modal-title">Sửa thông báo</h5><button class="close" data-dismiss="modal">×</button></div>
    <div class="modal-body">@include('Admin.Partials.AnnouncementForm', ['announcement' => $announcement])</div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Hủy</button><button class="btn btn-primary">Cập nhật</button></div>
</form></div></div></div>
@endforeach
@endsection
