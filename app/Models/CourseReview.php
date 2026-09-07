<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CourseReview extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'rating',
        'comment',
        'is_visible',
    ];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'is_visible' => 'boolean'];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
