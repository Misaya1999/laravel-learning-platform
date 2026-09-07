<?php

namespace Tests\Feature;

use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\BlogRating;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_blog_only_displays_published_posts_and_supports_search(): void
    {
        $admin = $this->admin();
        $category = Category::create(['name' => 'Kinh tế']);
        $published = $this->makeBlogPost($admin, ['title' => 'Quản lý tài chính', 'slug' => 'quan-ly-tai-chinh', 'category_id' => $category->id]);
        $draft = $this->makeBlogPost($admin, ['title' => 'Bản nháp nội bộ', 'slug' => 'ban-nhap-noi-bo', 'is_published' => false, 'published_at' => null]);

        $this->get(route('site.blog.index'))->assertOk()->assertSee($published->title)->assertDontSee($draft->title);
        $this->get(route('site.blog.index', ['search' => 'tài chính']))->assertOk()->assertSee($published->title);
        $this->get(route('site.blog.show', $published->slug))->assertOk()->assertSee($published->title);
        $this->get(route('site.blog.show', $draft->slug))->assertNotFound();
    }

    public function test_admin_can_create_update_and_delete_blog_posts(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $category = Category::create(['name' => 'Lập trình']);

        $this->actingAs($admin)->post(route('Admin.Blog.Store'), [
            'title' => 'Học Laravel thực tế',
            'category_id' => $category->id,
            'excerpt' => 'Lộ trình học Laravel.',
            'content' => 'Nội dung bài viết đầy đủ.',
            'image' => UploadedFile::fake()->createWithContent(
                'blog.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScLttAAAAABJRU5ErkJggg=='),
            ),
            'is_published' => '1',
        ])->assertSessionHas('success');

        $post = BlogPost::firstOrFail();
        $this->assertSame('hoc-laravel-thuc-te', $post->slug);
        Storage::disk('public')->assertExists($post->image);
        $this->actingAs($admin)->get(route('Admin.Blog'))->assertOk()->assertSee('Học Laravel thực tế');

        $this->actingAs($admin)->put(route('Admin.Blog.Update', $post), [
            'title' => 'Laravel cho người mới',
            'category_id' => $category->id,
            'content' => 'Nội dung đã cập nhật.',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('blog_posts', ['id' => $post->id, 'slug' => 'laravel-cho-nguoi-moi', 'is_published' => false]);
        $image = $post->image;
        $this->actingAs($admin)->delete(route('Admin.Blog.Delete', $post))->assertSessionHas('success');
        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
        Storage::disk('public')->assertMissing($image);
    }

    public function test_verified_user_can_rate_once_update_rating_and_add_comments(): void
    {
        $post = $this->makeBlogPost($this->admin());
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('site.blog.rating', $post), ['rating' => 4])->assertSessionHas('blog_success');
        $this->actingAs($user)->post(route('site.blog.rating', $post), ['rating' => 5])->assertSessionHas('blog_success');
        $this->actingAs($user)->post(route('site.blog.comment', $post), ['content' => 'Bài viết rất hữu ích.'])->assertSessionHas('blog_success');

        $this->assertDatabaseCount('blog_ratings', 1);
        $this->assertDatabaseHas('blog_ratings', ['blog_post_id' => $post->id, 'user_id' => $user->id, 'rating' => 5]);
        $this->assertDatabaseHas('blog_comments', ['blog_post_id' => $post->id, 'content' => 'Bài viết rất hữu ích.']);
        $this->get(route('site.blog.show', $post->slug))->assertOk()->assertSee('Bài viết rất hữu ích.');
    }

    public function test_guests_and_unverified_users_cannot_interact_with_blog(): void
    {
        $post = $this->makeBlogPost($this->admin());

        $this->post(route('site.blog.comment', $post), ['content' => 'Không được phép'])->assertRedirect(route('login'));

        $unverified = User::factory()->unverified()->create();
        $this->actingAs($unverified)->post(route('site.blog.rating', $post), ['rating' => 5])->assertRedirect(route('verification.notice'));
        $this->actingAs($unverified)->post(route('site.blog.comment', $post), ['content' => 'Không được phép'])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('blog_ratings', 0);
        $this->assertDatabaseCount('blog_comments', 0);
    }

    public function test_admin_can_hide_comments_and_delete_blog_ratings(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $post = $this->makeBlogPost($admin);
        $comment = BlogComment::create(['blog_post_id' => $post->id, 'user_id' => $user->id, 'content' => 'Nội dung cần kiểm duyệt']);
        $rating = BlogRating::create(['blog_post_id' => $post->id, 'user_id' => $user->id, 'rating' => 3]);

        $this->actingAs($admin)->patch(route('Admin.Blog.Comment.Visibility', $comment))->assertSessionHas('success');
        $this->assertDatabaseHas('blog_comments', ['id' => $comment->id, 'is_visible' => false]);
        $this->get(route('site.blog.show', $post->slug))->assertDontSee('Nội dung cần kiểm duyệt');

        $this->actingAs($admin)->delete(route('Admin.Blog.Rating.Delete', $rating))->assertSessionHas('success');
        $this->actingAs($admin)->delete(route('Admin.Blog.Comment.Delete', $comment))->assertSessionHas('success');
        $this->assertDatabaseMissing('blog_ratings', ['id' => $rating->id]);
        $this->assertDatabaseMissing('blog_comments', ['id' => $comment->id]);
    }

    public function test_rating_validation_and_draft_interactions_are_rejected(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $published = $this->makeBlogPost($admin);
        $draft = $this->makeBlogPost($admin, ['slug' => 'ban-nhap', 'is_published' => false, 'published_at' => null]);

        $this->actingAs($user)->post(route('site.blog.rating', $published), ['rating' => 6])->assertSessionHasErrors('rating');
        $this->actingAs($user)->post(route('site.blog.comment', $draft), ['content' => 'Nội dung'])->assertNotFound();
    }

    public function test_admin_can_publish_formatted_blog_content_and_unsafe_html_is_removed(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('Admin.Blog.Store'), [
            'title' => 'Bài viết có định dạng',
            'content' => '<h2>Tiêu đề chính</h2><p>Nội dung <strong>in đậm</strong> và <a href="https://example.com">liên kết</a>.</p><ul><li>Ý đầu tiên</li></ul><script>alert("xss")</script><img src="javascript:alert(1)" onerror="alert(1)">',
            'is_published' => '1',
        ])->assertSessionHas('success');

        $post = BlogPost::firstOrFail();

        $this->assertStringContainsString('<h2>Tiêu đề chính</h2>', $post->content);
        $this->assertStringContainsString('<strong>in đậm</strong>', $post->content);
        $this->assertStringNotContainsString('<script', $post->content);
        $this->assertStringNotContainsString('javascript:', $post->content);
        $this->assertStringNotContainsString('onerror', $post->content);

        $this->get(route('site.blog.show', $post->slug))
            ->assertOk()
            ->assertSee('<h2>Tiêu đề chính</h2>', false)
            ->assertSee('<strong>in đậm</strong>', false)
            ->assertDontSee('<script>alert', false);
    }

    public function test_plain_text_blog_content_still_preserves_line_breaks(): void
    {
        $post = $this->makeBlogPost($this->admin(), ['content' => "Dòng đầu tiên\nDòng thứ hai"]);

        $this->get(route('site.blog.show', $post->slug))
            ->assertOk()
            ->assertSee("Dòng đầu tiên<br />\nDòng thứ hai", false);
    }

    public function test_admin_can_upload_images_directly_from_ckeditor(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson(route('Admin.Blog.Image.Upload'), [
            'upload' => UploadedFile::fake()->createWithContent(
                'inline.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScLttAAAAABJRU5ErkJggg=='),
            ),
        ]);

        $response->assertOk()->assertJsonStructure(['url']);
        $this->assertStringContainsString('/storage/uploads/blog/content/', $response->json('url'));
        $this->assertCount(1, Storage::disk('public')->files('uploads/blog/content'));
    }

    public function test_regular_user_cannot_upload_ckeditor_images(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('Admin.Blog.Image.Upload'))
            ->assertForbidden();
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        return $admin;
    }

    private function makeBlogPost(User $author, array $attributes = []): BlogPost
    {
        return BlogPost::create(array_merge([
            'user_id' => $author->id,
            'title' => 'Bài viết chia sẻ kiến thức',
            'slug' => 'bai-viet-' . uniqid(),
            'content' => 'Nội dung bài viết dành cho học viên.',
            'is_published' => true,
            'published_at' => now()->subMinute(),
        ], $attributes));
    }
}
