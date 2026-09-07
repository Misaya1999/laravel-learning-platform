<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\CourseReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrolled_user_can_create_and_update_a_review(): void
    {
        $user = User::factory()->create();
        $course = Course::create(['name' => 'Khóa học đánh giá']);
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);

        $this->actingAs($user)->post(route('site.course.review', $course), [
            'rating' => 4,
            'comment' => 'Nội dung dễ hiểu.',
        ])->assertSessionHas('review_success');

        $this->actingAs($user)->post(route('site.course.review', $course), [
            'rating' => 5,
            'comment' => 'Rất hữu ích.',
        ])->assertSessionHas('review_success');

        $this->assertDatabaseCount('course_reviews', 1);
        $this->assertDatabaseHas('course_reviews', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'rating' => 5,
            'comment' => 'Rất hữu ích.',
        ]);
    }

    public function test_user_without_enrollment_cannot_review(): void
    {
        $user = User::factory()->create();
        $course = Course::create(['name' => 'Khóa học chưa mua']);

        $this->actingAs($user)->post(route('site.course.review', $course), [
            'rating' => 5,
        ])->assertForbidden();

        $this->assertDatabaseCount('course_reviews', 0);
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $user = User::factory()->create();
        $course = Course::create(['name' => 'Khóa học giới hạn sao']);
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);

        $this->actingAs($user)->post(route('site.course.review', $course), [
            'rating' => 6,
        ])->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('course_reviews', 0);
    }

    public function test_admin_can_search_filter_and_hide_a_review(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create(['name' => 'Nguyễn Học Viên']);
        $course = Course::create(['name' => 'Khóa học kiểm duyệt', 'status' => Course::STATUS_PUBLISHED]);
        $review = CourseReview::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'rating' => 4,
            'comment' => 'Nội dung cần kiểm duyệt',
        ]);

        $this->actingAs($admin)->get(route('Admin.Review', ['search' => 'Học Viên', 'rating' => 4]))
            ->assertOk()->assertSee('Nội dung cần kiểm duyệt');
        $this->actingAs($admin)->patch(route('Admin.Review.Visibility', $review))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('course_reviews', ['id' => $review->id, 'is_visible' => false]);
        $this->assertFalse($course->reviews()->visible()->whereKey($review->id)->exists());

        $stats = Course::withLearningStats()->findOrFail($course->id);
        $this->assertSame(0, $stats->reviews_count);
        $this->assertNull($stats->reviews_avg_rating);
    }

    public function test_admin_can_restore_or_delete_review_but_user_cannot_access_moderation(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create();
        $course = Course::create(['name' => 'Khóa học quản lý']);
        $review = CourseReview::create(['user_id' => $user->id, 'course_id' => $course->id, 'rating' => 5, 'is_visible' => false]);

        $this->actingAs($user)->patch(route('Admin.Review.Visibility', $review))->assertForbidden();
        $this->actingAs($admin)->patch(route('Admin.Review.Visibility', $review))->assertSessionHas('success');
        $this->assertDatabaseHas('course_reviews', ['id' => $review->id, 'is_visible' => true]);

        $this->actingAs($admin)->delete(route('Admin.Review.Delete', $review))->assertSessionHas('success');
        $this->assertDatabaseMissing('course_reviews', ['id' => $review->id]);
    }
}
