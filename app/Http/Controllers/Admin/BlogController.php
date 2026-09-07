<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\BlogRating;
use App\Models\Category;
use App\Support\BlogContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = BlogPost::query()
            ->with(['author', 'category'])
            ->withCount(['ratings', 'comments'])
            ->withAvg('ratings', 'rating')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());
                $query->where(fn ($query) => $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhereHas('author', fn ($author) => $author->where('name', 'like', "%{$search}%")));
            })
            ->when($request->status === 'published', fn ($query) => $query->where('is_published', true))
            ->when($request->status === 'draft', fn ($query) => $query->where('is_published', false))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $comments = BlogComment::with(['user', 'post'])->latest()->limit(15)->get();
        $ratings = BlogRating::with(['user', 'post'])->latest()->limit(10)->get();

        return view('Admin.Blog', compact('posts', 'categories', 'comments', 'ratings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('uploads/blog', 'public');
        }

        BlogPost::create($data);

        return back()->with('success', 'Đã thêm bài viết blog.');
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['title'], $post);
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? ($post->published_at ?? now()) : null;

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }

            $data['image'] = $request->file('image')->store('uploads/blog', 'public');
        }

        $post->update($data);

        return back()->with('success', 'Đã cập nhật bài viết blog.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $image = $post->image;
        $post->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return back()->with('success', 'Đã xóa bài viết blog.');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
        ], [
            'upload.required' => 'Vui lòng chọn hình ảnh cần tải lên.',
            'upload.image' => 'Tệp tải lên phải là hình ảnh.',
            'upload.max' => 'Hình ảnh không được vượt quá 10 MB.',
        ]);

        $path = $request->file('upload')->store('uploads/blog/content', 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }

    public function toggleComment(BlogComment $comment): RedirectResponse
    {
        $comment->update(['is_visible' => ! $comment->is_visible]);

        return back()->with('success', $comment->is_visible ? 'Đã hiển thị bình luận.' : 'Đã ẩn bình luận.');
    }

    public function destroyComment(BlogComment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'Đã xóa bình luận.');
    }

    public function destroyRating(BlogRating $rating): RedirectResponse
    {
        $rating->delete();

        return back()->with('success', 'Đã xóa đánh giá bài viết.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:category,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:100000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'content.required' => 'Vui lòng nhập nội dung bài viết.',
            'image.image' => 'Ảnh đại diện phải là tệp hình ảnh.',
            'image.max' => 'Ảnh đại diện không được vượt quá 5 MB.',
        ]);

        $data['content'] = BlogContentSanitizer::sanitize($data['content']);

        if (trim(html_entity_decode(strip_tags($data['content']), ENT_QUOTES | ENT_HTML5, 'UTF-8')) === ''
            && ! preg_match('/<img\b/i', $data['content'])) {
            throw ValidationException::withMessages([
                'content' => 'Vui lòng nhập nội dung bài viết.',
            ]);
        }

        return $data;
    }

    private function uniqueSlug(string $title, ?BlogPost $post = null): string
    {
        $base = Str::slug($title) ?: 'bai-viet';
        $slug = $base;
        $counter = 2;

        while (BlogPost::where('slug', $slug)->when($post, fn ($query) => $query->whereKeyNot($post->id))->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
