@extends('Site.Layout.Index')

@section('title', 'Blog kiến thức — KhoaHocPlus')
@section('description', 'Khám phá bài viết, kinh nghiệm học tập và kiến thức hữu ích từ KhoaHocPlus.')

@section('content')
<section class="blog-hero"><div class="site-container blog-hero-inner"><div><span class="eyebrow coral-text">Góc chia sẻ kiến thức</span><h1>Đọc thêm một chút.<br><em>Tiến xa hơn một bước.</em></h1><p>Những bài viết thực tế về học tập, công nghệ và kỹ năng để bạn phát triển mỗi ngày.</p></div><span class="blog-hero-mark" aria-hidden="true">Aa</span></div></section>

<section class="blog-index-section"><div class="site-container">
    <div class="blog-toolbar"><div class="blog-category-list"><a href="{{ route('site.blog.index', array_filter(['search' => request('search')])) }}" class="{{ ! request('category') ? 'is-active' : '' }}">Tất cả</a>@foreach ($categories as $category)<a href="{{ route('site.blog.index', array_filter(['category' => $category->id, 'search' => request('search')])) }}" class="{{ (int) request('category') === $category->id ? 'is-active' : '' }}">{{ $category->name }}</a>@endforeach</div><form action="{{ route('site.blog.index') }}" class="blog-search">@if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif<input type="search" name="search" value="{{ request('search') }}" placeholder="Tìm bài viết..."><button aria-label="Tìm bài viết">⌕</button></form></div>

    <div class="blog-list-heading"><span>{{ $posts->total() }} bài viết</span><strong>Mới cập nhật</strong></div>

    <div class="blog-card-grid">
        @forelse ($posts as $post)
            <article class="blog-card"><a href="{{ route('site.blog.show', $post->slug) }}" class="blog-card-cover {{ $post->image ? 'has-image' : '' }}">@if($post->image)<img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}">@else<span aria-hidden="true">{{ mb_strtoupper(mb_substr($post->category?->name ?? 'B', 0, 1)) }}</span>@endif<span class="blog-card-category">{{ $post->category?->name ?? 'Kiến thức' }}</span></a><div class="blog-card-body"><div class="blog-card-meta"><span>{{ $post->published_at->format('d/m/Y') }}</span><span>{{ $post->readingMinutes() }} phút đọc</span></div><h2><a href="{{ route('site.blog.show', $post->slug) }}">{{ $post->title }}</a></h2><p>{{ Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 120) }}</p><div class="blog-card-footer"><span>{{ $post->author?->name ?? 'KhoaHocPlus' }}</span><span>@if($post->ratings_count)★ {{ number_format((float) $post->ratings_avg_rating, 1) }} · @endif{{ $post->comments_count }} bình luận</span></div></div></article>
        @empty<div class="blog-empty"><span>⌕</span><h2>Chưa tìm thấy bài viết</h2><p>Thử tìm bằng từ khóa khác hoặc quay lại sau nhé.</p>@if(request()->hasAny(['search', 'category']))<a href="{{ route('site.blog.index') }}">Xem tất cả bài viết →</a>@endif</div>@endforelse
    </div>

    <div class="site-pagination">{{ $posts->links() }}</div>
</div></section>
@endsection
