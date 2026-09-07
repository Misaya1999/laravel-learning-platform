<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonView extends Model
{
    protected $fillable = ['user_id', 'lesson_id', 'last_viewed_at'];

    protected function casts(): array
    {
        return ['last_viewed_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function lesson(): BelongsTo { return $this->belongsTo(Lesson::class); }
}
