<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_learning_stats_include_rating_and_active_students(): void
    {
        $course = Course::create(['name' => 'Khóa học thống kê']);
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $cancelledUser = User::factory()->create();

        Enrollment::create(['user_id' => $firstUser->id, 'course_id' => $course->id]);
        Enrollment::create(['user_id' => $secondUser->id, 'course_id' => $course->id]);
        Enrollment::create([
            'user_id' => $cancelledUser->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_CANCELLED,
        ]);

        CourseReview::create(['user_id' => $firstUser->id, 'course_id' => $course->id, 'rating' => 4]);
        CourseReview::create(['user_id' => $secondUser->id, 'course_id' => $course->id, 'rating' => 5]);

        $stats = Course::withLearningStats()->findOrFail($course->id);

        $this->assertSame(2, $stats->students_count);
        $this->assertSame(2, $stats->reviews_count);
        $this->assertSame(4.5, (float) $stats->reviews_avg_rating);
    }
}
