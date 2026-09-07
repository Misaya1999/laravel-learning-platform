<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\BlogRating;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = BlogPost::published()
            ->with(['author', 'category'])
            ->withCount(['ratings', 'comments' => fn ($query) => $query->visible()])
            ->withAvg('ratings', 'rating')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());
                $query->where(fn ($query) => $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%"));
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = Category::whereHas('blogPosts', fn ($query) => $query->published())
            ->orderBy('name')
            ->get();

        return view('Site.Blog.Index', compact('posts', 'categories'));
    }

    public function show(string $slug, Request $request): View
    {
        $post = BlogPost::published()
            ->with(['author', 'category'])
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->where('slug', $slug)
            ->firstOrFail();

        $comments = $post->comments()->visible()->with('user')->latest()->paginate(12);
        $currentRating = $request->user()
            ? $post->ratings()->where('user_id', $request->user()->id)->first()
            : null;
        $relatedPosts = BlogPost::published()->whereKeyNot($post->id)
            ->when($post->category_id, fn ($query) => $query->where('category_id', $post->category_id))
            ->latest('published_at')->limit(3)->get();

        return view('Site.Blog.Show', compact('post', 'comments', 'currentRating', 'relatedPosts'));
    }

    public function rate(Request $request, BlogPost $post): RedirectResponse
    {
        $this->ensurePublished($post);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao.',
            'rating.between' => 'Đánh giá phải từ 1 đến 5 sao.',
        ]);

        BlogRating::updateOrCreate(
            ['blog_post_id' => $post->id, 'user_id' => $request->user()->id],
            $data,
        );

        return back()->with('blog_success', 'Đã lưu đánh giá của bạn.');
    }

    public function comment(Request $request, BlogPost $post): RedirectResponse
    {
        $this->ensurePublished($post);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ], [
            'content.required' => 'Vui lòng nhập nội dung bình luận.',
            'content.max' => 'Bình luận không được vượt quá 2.000 ký tự.',
        ]);

        BlogComment::create([
            'blog_post_id' => $post->id,
            'user_id' => $request->user()->id,
            'content' => $data['content'],
        ]);

        return back()->with('blog_success', 'Đã đăng bình luận của bạn.');
    }

    private function ensurePublished(BlogPost $post): void
    {
        abort_unless($post->is_published && $post->published_at?->isPast(), 404);
    }
}
