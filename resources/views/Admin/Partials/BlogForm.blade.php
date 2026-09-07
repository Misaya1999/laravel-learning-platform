<div class="form-group">
    <label for="blog-title-{{ $post?->id ?? 'new' }}">Tiêu đề bài viết <span class="text-danger">*</span></label>
    <input id="blog-title-{{ $post?->id ?? 'new' }}" name="title" class="form-control" value="{{ old('title', $post?->title) }}" maxlength="255" required placeholder="Ví dụ: 5 nguyên tắc quản lý tài chính cá nhân">
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Danh mục</label>
        <select name="category_id" class="form-control">
            <option value="">Không chọn danh mục</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $post?->category_id) === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        <label>Ảnh đại diện</label>
        <input type="file" name="image" class="form-control-file" accept="image/jpeg,image/png,image/webp">
        <small class="text-muted">JPG, PNG hoặc WEBP; tối đa 5 MB.</small>
    </div>
</div>
@if ($post?->image)
    <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" style="width:160px;height:90px;object-fit:cover;border-radius:5px;margin-bottom:16px">
@endif
<div class="form-group">
    <label>Mô tả ngắn</label>
    <textarea name="excerpt" class="form-control" rows="3" maxlength="500" placeholder="Tóm tắt ngắn để hiển thị trong danh sách blog">{{ old('excerpt', $post?->excerpt) }}</textarea>
</div>
<div class="form-group">
    <label for="blog-content-{{ $post?->id ?? 'new' }}">Nội dung bài viết <span class="text-danger">*</span></label>
    <div class="blog-ckeditor-wrapper">
        <textarea id="blog-content-{{ $post?->id ?? 'new' }}" name="content" class="form-control blog-ckeditor" data-blog-ckeditor data-upload-url="{{ route('Admin.Blog.Image.Upload') }}" rows="12" placeholder="Bắt đầu viết bài blog của bạn...">{{ old('content', $post?->content) }}</textarea>
        <div class="blog-ckeditor-status" data-blog-editor-status></div>
    </div>
</div>
<div class="custom-control custom-checkbox">
    <input type="checkbox" class="custom-control-input" id="blog-published-{{ $post?->id ?? 'new' }}" name="is_published" value="1" @checked(old('is_published', $post?->is_published ?? true))>
    <label class="custom-control-label" for="blog-published-{{ $post?->id ?? 'new' }}">Đăng bài viết lên website</label>
</div>
