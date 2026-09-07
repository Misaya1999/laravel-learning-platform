@extends('Admin.Layout.Index')

@section('content')
<style>
    .admin-blog-title { display:flex;align-items:center;gap:12px;min-width:260px; }.admin-blog-thumb { width:68px;height:48px;flex:0 0 68px;overflow:hidden;display:grid;place-items:center;border:1px solid #e6eaf0;border-radius:5px;background:#eef2ff;color:#3858e9;font-size:20px;font-weight:700; }.admin-blog-thumb img { width:100%;height:100%;object-fit:cover; }.admin-blog-title small { display:block;margin-top:3px;color:#8792a2; }.admin-blog-stars { color:#e6a529;letter-spacing:1px; }.admin-blog-comment { max-width:360px;white-space:normal; }
    .blog-ckeditor-wrapper { --ck-color-base-border:#dfe4ea;--ck-color-focus-border:#2962ff;--ck-border-radius:5px; }.blog-ckeditor-wrapper .ck.ck-toolbar { border-radius:5px 5px 0 0!important;background:#f8f9fb; }.blog-ckeditor-wrapper .ck.ck-editor__editable_inline { min-height:300px;max-height:520px;padding:16px 19px; }.blog-ckeditor-wrapper .ck.ck-content { color:#344054;font-size:14px;line-height:1.8; }.blog-ckeditor-wrapper .ck.ck-content img { max-width:100%; }.blog-ckeditor-status { min-height:28px;padding:6px 10px;display:flex;justify-content:flex-end;border:1px solid #dfe4ea;border-top:0;border-radius:0 0 5px 5px;background:#fafbfc;color:#8390a1;font-size:10px; }.blog-ckeditor-wrapper .ck-word-count { display:flex;gap:14px; }.ck.ck-balloon-panel { z-index:1100!important; }.modal .ck-body-wrapper { position:relative;z-index:1100; }.blog-ckeditor-wrapper.has-error .ck-editor__editable { border-color:#e74c3c!important; }
    @media(max-width:575px) { .blog-ckeditor-wrapper .ck.ck-editor__editable_inline { min-height:240px;padding:12px; } }
</style>

<div class="page-breadcrumb"><div class="row align-items-center"><div class="col-6"><h4 class="page-title mb-0">Quản lý blog</h4></div><div class="col-6 text-right"><button class="btn btn-primary" data-toggle="modal" data-target="#addBlogModal">+ Thêm bài viết</button></div></div></div>

<div class="container-fluid">
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="card"><div class="card-body">
        <form method="GET" class="form-row align-items-end mb-4">
            <div class="col-md-6"><label>Tìm kiếm bài viết</label><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Tiêu đề, mô tả hoặc tác giả"></div>
            <div class="col-md-3"><label>Trạng thái</label><select name="status" class="form-control"><option value="">Tất cả trạng thái</option><option value="published" @selected(request('status') === 'published')>Đã đăng</option><option value="draft" @selected(request('status') === 'draft')>Bản nháp</option></select></div>
            <div class="col-md-3"><button class="btn btn-dark">Tìm kiếm</button> <a href="{{ route('Admin.Blog') }}" class="btn btn-light">Đặt lại</a></div>
        </form>

        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Bài viết</th><th>Danh mục</th><th>Tác giả</th><th>Đánh giá</th><th>Bình luận</th><th>Trạng thái</th><th>Ngày đăng</th><th>Thao tác</th></tr></thead><tbody>
            @forelse ($posts as $post)
                <tr>
                    <td><div class="admin-blog-title"><span class="admin-blog-thumb">@if ($post->image)<img src="{{ asset('storage/' . $post->image) }}" alt="">@else ¶ @endif</span><span><strong>{{ $post->title }}</strong><small>{{ Illuminate\Support\Str::limit($post->excerpt, 62) }}</small></span></div></td>
                    <td>{{ $post->category?->name ?? '—' }}</td><td>{{ $post->author?->name ?? '—' }}</td>
                    <td>@if ($post->ratings_count)<span class="admin-blog-stars">★</span> {{ number_format((float) $post->ratings_avg_rating, 1) }} <small>({{ $post->ratings_count }})</small>@else — @endif</td>
                    <td>{{ $post->comments_count }}</td><td><span class="badge badge-{{ $post->is_published ? 'success' : 'secondary' }}">{{ $post->is_published ? 'Đã đăng' : 'Bản nháp' }}</span></td><td>{{ $post->published_at?->format('d/m/Y') ?? '—' }}</td>
                    <td class="text-nowrap">@if ($post->is_published)<a href="{{ route('site.blog.show', $post->slug) }}" target="_blank" class="btn btn-sm btn-info">Xem</a>@endif <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editBlog{{ $post->id }}">Sửa</button> <form method="POST" action="{{ route('Admin.Blog.Delete', $post) }}" class="d-inline" onsubmit="return confirm('Xóa bài viết và toàn bộ bình luận, đánh giá liên quan?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Xóa</button></form></td>
                </tr>
            @empty<tr><td colspan="8" class="text-center py-5">Chưa có bài viết blog. Hãy thêm bài viết đầu tiên.</td></tr>@endforelse
        </tbody></table></div><div class="mt-3">{{ $posts->links() }}</div>
    </div></div>

    <div class="card"><div class="card-body"><h4 class="card-title mb-4">Bình luận bài viết gần đây</h4>
        <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Người dùng</th><th>Bài viết</th><th>Nội dung</th><th>Trạng thái</th><th>Thời gian</th><th>Thao tác</th></tr></thead><tbody>
            @forelse ($comments as $comment)
                <tr><td>{{ $comment->user?->name ?? '—' }}</td><td>{{ Illuminate\Support\Str::limit($comment->post?->title, 45) }}</td><td class="admin-blog-comment">{{ Illuminate\Support\Str::limit($comment->content, 140) }}</td><td><span class="badge badge-{{ $comment->is_visible ? 'success' : 'secondary' }}">{{ $comment->is_visible ? 'Hiển thị' : 'Đã ẩn' }}</span></td><td>{{ $comment->created_at->format('d/m/Y H:i') }}</td><td class="text-nowrap"><form method="POST" action="{{ route('Admin.Blog.Comment.Visibility', $comment) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary">{{ $comment->is_visible ? 'Ẩn' : 'Hiện' }}</button></form> <form method="POST" action="{{ route('Admin.Blog.Comment.Delete', $comment) }}" class="d-inline" onsubmit="return confirm('Xóa bình luận này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form></td></tr>
            @empty<tr><td colspan="6" class="text-center py-4">Chưa có bình luận.</td></tr>@endforelse
        </tbody></table></div>
    </div></div>

    <div class="card"><div class="card-body"><h4 class="card-title mb-4">Đánh giá bài viết gần đây</h4>
        <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Người dùng</th><th>Bài viết</th><th>Đánh giá</th><th>Thời gian</th><th>Thao tác</th></tr></thead><tbody>
            @forelse ($ratings as $rating)<tr><td>{{ $rating->user?->name ?? '—' }}</td><td>{{ $rating->post?->title ?? '—' }}</td><td class="admin-blog-stars">{{ str_repeat('★', $rating->rating) }}{{ str_repeat('☆', 5 - $rating->rating) }}</td><td>{{ $rating->updated_at->format('d/m/Y H:i') }}</td><td><form method="POST" action="{{ route('Admin.Blog.Rating.Delete', $rating) }}" onsubmit="return confirm('Xóa đánh giá này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form></td></tr>@empty<tr><td colspan="5" class="text-center py-4">Chưa có đánh giá.</td></tr>@endforelse
        </tbody></table></div>
    </div></div>
</div>

<div class="modal fade" id="addBlogModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" action="{{ route('Admin.Blog.Store') }}" enctype="multipart/form-data">@csrf<div class="modal-header"><h5 class="modal-title">Thêm bài viết blog</h5><button type="button" class="close" data-dismiss="modal">×</button></div><div class="modal-body">@include('Admin.Partials.BlogForm', ['post' => null])</div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Hủy</button><button class="btn btn-primary">Lưu bài viết</button></div></form></div></div></div>

@foreach ($posts as $post)
    <div class="modal fade" id="editBlog{{ $post->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" action="{{ route('Admin.Blog.Update', $post) }}" enctype="multipart/form-data">@csrf @method('PUT')<div class="modal-header"><h5 class="modal-title">Chỉnh sửa bài viết</h5><button type="button" class="close" data-dismiss="modal">×</button></div><div class="modal-body">@include('Admin.Partials.BlogForm', ['post' => $post])</div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Hủy</button><button class="btn btn-primary">Cập nhật bài viết</button></div></form></div></div></div>
@endforeach
@endsection

@push('scripts')
    @vite('resources/js/blog-editor.js')
@endpush
