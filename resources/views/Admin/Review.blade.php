@extends('Admin.Layout.Index')

@section('content')
<style>
    .container-fluid { padding: 20px 20px 0 !important; }.review-toolbar { display:flex; gap:10px; flex-wrap:wrap; }.review-toolbar input { min-width:260px; flex:1; }.review-stars { color:#e5a100; white-space:nowrap; letter-spacing:1px; }.review-comment { max-width:360px; line-height:1.5; }.review-summary { display:flex; gap:22px; flex-wrap:wrap; }.review-summary strong { margin-right:5px; font-size:20px; }.review-user { display:flex; align-items:center; gap:10px; }.review-user-avatar { width:34px; height:34px; border-radius:50%; overflow:hidden; display:grid; place-items:center; color:#fff; background:#405de6; font-weight:700; }.review-user-avatar img { width:100%; height:100%; object-fit:cover; }.review-full-content { padding:16px; white-space:pre-line; line-height:1.7; background:#f7f7f7; border:1px solid #e7e7e7; }
</style>
<div class="page-breadcrumb"><div class="row"><div class="col-6"><h4 class="page-title">Quản lý đánh giá</h4></div><div class="col-6 text-right"><a href="{{ route('Admin.Dashboard') }}">Dashboard</a> / Đánh giá</div></div></div>
<div class="container-fluid">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="card-body review-summary"><span><strong>{{ $summary['all'] }}</strong> tổng đánh giá</span><span><strong>{{ $summary['visible'] }}</strong> đang hiển thị</span><span><strong>{{ $summary['hidden'] }}</strong> đã ẩn</span></div></div>
    <div class="card">
        <div class="card-body"><form method="GET" class="review-toolbar">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tìm user, email, khóa học hoặc nội dung...">
            <select name="rating" class="form-control" style="width:auto"><option value="">Tất cả số sao</option>@for($star=5;$star>=1;$star--)<option value="{{ $star }}" @selected((string)request('rating')===(string)$star)>{{ $star }} sao</option>@endfor</select>
            <select name="visibility" class="form-control" style="width:auto"><option value="">Tất cả trạng thái</option><option value="visible" @selected(request('visibility')==='visible')>Đang hiển thị</option><option value="hidden" @selected(request('visibility')==='hidden')>Đã ẩn</option></select>
            <select name="sort" class="form-control" style="width:auto"><option value="latest">Mới nhất</option><option value="oldest" @selected(request('sort')==='oldest')>Cũ nhất</option><option value="rating_desc" @selected(request('sort')==='rating_desc')>Sao cao nhất</option><option value="rating_asc" @selected(request('sort')==='rating_asc')>Sao thấp nhất</option></select>
            <button class="btn btn-primary">Tìm kiếm</button>@if(request()->hasAny(['search','rating','visibility','sort']))<a href="{{ route('Admin.Review') }}" class="btn btn-outline-secondary">Reset</a>@endif
        </form></div>
        <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Người dùng</th><th>Khóa học</th><th>Đánh giá</th><th>Nội dung</th><th>Trạng thái</th><th>Ngày gửi</th><th>Thao tác</th></tr></thead><tbody>
        @forelse($reviews as $review)
            <tr>
                <td><div class="review-user"><span class="review-user-avatar">@if($review->user?->avatar)<img src="{{ asset('storage/'.$review->user->avatar) }}" alt="">@else{{ mb_strtoupper(mb_substr($review->user?->name ?? '?',0,1)) }}@endif</span><span><strong>{{ $review->user?->name ?? 'Người dùng đã xóa' }}</strong><br><small class="text-muted">{{ $review->user?->email }}</small></span></div></td>
                <td><strong>{{ $review->course?->name ?? 'Khóa học đã xóa' }}</strong></td>
                <td><span class="review-stars">{{ str_repeat('★',$review->rating) }}{{ str_repeat('☆',5-$review->rating) }}</span><br><small>{{ $review->rating }}/5</small></td>
                <td class="review-comment">{{ Illuminate\Support\Str::limit($review->comment ?: 'Không có nội dung', 100) }}</td>
                <td><span class="badge {{ $review->is_visible ? 'badge-success' : 'badge-secondary' }}">{{ $review->is_visible ? 'Đang hiển thị' : 'Đã ẩn' }}</span></td>
                <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                <td class="text-nowrap"><button class="btn btn-info btn-sm" data-toggle="modal" data-target="#reviewModal{{ $review->id }}">Xem nội dung</button> <form method="POST" action="{{ route('Admin.Review.Visibility',$review) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm {{ $review->is_visible ? 'btn-warning' : 'btn-success' }}">{{ $review->is_visible ? 'Ẩn' : 'Hiện lại' }}</button></form> <form method="POST" action="{{ route('Admin.Review.Delete',$review) }}" class="d-inline" onsubmit="return confirm('Xóa vĩnh viễn đánh giá này?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Xóa</button></form></td>
            </tr>
        @empty<tr><td colspan="7" class="text-center text-muted py-4">Không tìm thấy đánh giá phù hợp.</td></tr>@endforelse
        </tbody></table></div>
        <div class="card-body">{{ $reviews->links() }}</div>
    </div>
</div>

@foreach($reviews as $review)
<div class="modal fade" id="reviewModal{{ $review->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5>Đánh giá khóa học {{ $review->course?->name }}</h5><button type="button" class="close" data-dismiss="modal">×</button></div><div class="modal-body"><p><strong>{{ $review->user?->name }}</strong> · <span class="review-stars">{{ str_repeat('★',$review->rating) }}{{ str_repeat('☆',5-$review->rating) }}</span></p><div class="review-full-content">{{ $review->comment ?: 'Người dùng không viết nội dung nhận xét.' }}</div><small class="text-muted d-block mt-3">Gửi lúc {{ $review->created_at->format('d/m/Y H:i') }}</small></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button></div></div></div></div>
@endforeach
@endsection
