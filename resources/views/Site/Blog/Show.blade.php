@extends('Site.Layout.Index')

@section('title', $post->title . ' — KhoaHocPlus')
@section('description', $post->excerpt ?: Illuminate\Support\Str::limit(strip_tags($post->content), 155))

@section('content')
<article class="blog-article"><div class="site-container blog-article-container">
    <a href="{{ route('site.blog.index') }}" class="blog-back-link">← Quay lại blog</a>
    <header class="blog-article-heading"><span class="eyebrow coral-text">{{ $post->category?->name ?? 'Góc chia sẻ' }}</span><h1>{{ $post->title }}</h1><div class="blog-author-line"><span class="blog-author-avatar">@if($post->author?->avatar)<img src="{{ asset('storage/' . $post->author->avatar) }}" alt="">@else{{ mb_strtoupper(mb_substr($post->author?->name ?? 'K', 0, 1)) }}@endif</span><span><strong>{{ $post->author?->name ?? 'KhoaHocPlus' }}</strong><small>{{ $post->published_at->format('d/m/Y') }} · {{ $post->readingMinutes() }} phút đọc</small></span>@if($post->ratings_count)<span class="blog-heading-rating">★ {{ number_format((float) $post->ratings_avg_rating, 1) }} <small>({{ $post->ratings_count }})</small></span>@endif</div></header>

    @if($post->image)<div class="blog-article-cover"><img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}"></div>@endif
    @if($post->excerpt)<p class="blog-article-intro">{{ $post->excerpt }}</p>@endif
    <div class="blog-article-content">{!! $post->formattedContent() !!}</div>

    <section class="blog-engagement" id="binh-luan"><div class="blog-engagement-header"><span class="eyebrow coral-text">Chia sẻ suy nghĩ</span><h2>Bài viết này hữu ích chứ?</h2></div>
        @if(session('blog_success'))<div class="blog-feedback">{{ session('blog_success') }}</div>@endif
        @if($errors->any())<div class="blog-feedback is-error">{{ $errors->first() }}</div>@endif

        @auth
            @if(auth()->user()->hasVerifiedEmail())
                <form action="{{ route('site.blog.rating', $post) }}" method="POST" class="blog-rating-form">@csrf<strong>{{ $currentRating ? 'Đánh giá của bạn' : 'Chọn số sao' }}</strong><div class="rating-stars blog-rating-stars">@for($star=5;$star>=1;$star--)<input type="radio" id="blog-rating-{{ $star }}" name="rating" value="{{ $star }}" @checked((int) old('rating', $currentRating?->rating) === $star) onchange="this.form.submit()"><label for="blog-rating-{{ $star }}" title="{{ $star }} sao">★</label>@endfor</div><small>{{ $currentRating ? 'Bấm để thay đổi đánh giá' : 'Đánh giá từ 1 đến 5 sao' }}</small></form>
                <form action="{{ route('site.blog.comment', $post) }}" method="POST" class="blog-comment-form">@csrf<label for="blog-comment">Viết bình luận</label><textarea id="blog-comment" name="content" rows="4" maxlength="2000" required placeholder="Chia sẻ cảm nhận hoặc câu hỏi của bạn...">{{ old('content') }}</textarea><button class="primary-button">Đăng bình luận <span>→</span></button></form>
            @else<div class="blog-signin-prompt">Bạn cần <a href="{{ route('verification.notice') }}">xác minh email</a> trước khi đánh giá hoặc bình luận.</div>@endif
        @else<div class="blog-signin-prompt"><a href="{{ route('login') }}">Đăng nhập</a> để đánh giá và tham gia thảo luận.</div>@endauth

        <div class="blog-comments-heading"><h3>Bình luận</h3><span>{{ $comments->total() }}</span></div>
        <div class="blog-comments-list">@forelse($comments as $comment)<article class="blog-comment-item"><span class="blog-comment-avatar">@if($comment->user?->avatar)<img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="">@else{{ mb_strtoupper(mb_substr($comment->user?->name ?? 'U', 0, 1)) }}@endif</span><div><div class="blog-comment-meta"><strong>{{ $comment->user?->name ?? 'Người dùng' }}</strong><span>{{ $comment->created_at->diffForHumans() }}</span></div><p>{{ $comment->content }}</p></div></article>@empty<p class="blog-no-comments">Chưa có bình luận. Hãy là người đầu tiên chia sẻ.</p>@endforelse</div><div class="site-pagination">{{ $comments->links() }}</div>
    </section>
</div></article>

@if($relatedPosts->isNotEmpty())<section class="blog-related"><div class="site-container blog-article-container"><span class="eyebrow coral-text">Đọc thêm</span><h2>Bài viết cùng chủ đề</h2><div class="blog-related-list">@foreach($relatedPosts as $related)<a href="{{ route('site.blog.show', $related->slug) }}"><span>{{ $related->published_at->format('d/m/Y') }}</span><strong>{{ $related->title }}</strong><i>→</i></a>@endforeach</div></div></section>@endif
@endsection
