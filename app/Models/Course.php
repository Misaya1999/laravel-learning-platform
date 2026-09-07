<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Course extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_HIDDEN = 'hidden';

    protected $attributes = [
        'access_duration' => 12,
        'access_duration_unit' => 'months',
        'status' => self::STATUS_DRAFT,
    ];

    protected $fillable = [
        'category_id',
        'name',
        'image',
        'description',
        'price',
        'access_duration',
        'access_duration_unit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'access_duration' => 'integer',
        ];
    }

    public function accessPeriodLabel(): string
    {
        return $this->access_duration . ($this->access_duration_unit === 'days' ? ' ngày' : ' tháng');
    }

    public function calculateExpiresAt(?Carbon $from = null): Carbon
    {
        $from ??= now();

        return $this->access_duration_unit === 'days'
            ? $from->copy()->addDays($this->access_duration)
            : $from->copy()->addMonthsNoOverflow($this->access_duration);
    }

    public function scopeWithLearningStats(Builder $query): Builder
    {
        return $query
            ->withCount([
                'sections',
                'lessons',
                'reviews as reviews_count' => fn ($query) => $query->visible(),
                'enrollments as students_count' => fn ($query) => $query->whereIn('status', [
                    Enrollment::STATUS_ACTIVE,
                    Enrollment::STATUS_COMPLETED,
                ]),
            ])
            ->withSum('lessons', 'duration_minutes')
            ->withAvg(['reviews as reviews_avg_rating' => fn ($query) => $query->visible()], 'rating');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function sections()
    {
        return $this->hasMany(CourseSection::class)
            ->orderBy('position');
    }

    public function lessons()
    {
        return $this->hasManyThrough(
            Lesson::class,
            CourseSection::class,
            'course_id',
            'course_section_id'
        );
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot(['status', 'enrolled_at', 'expires_at', 'completed_at'])
            ->withTimestamps();
    }

    public function mentors(): BelongsToMany
    {
        return $this->belongsToMany(Mentor::class, 'course_mentor');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }
}
