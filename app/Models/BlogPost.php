<?php

namespace App\Models;

use App\Support\BlogContentSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogPost extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'excerpt', 'content',
        'image', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'published_at' => 'datetime'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(BlogRating::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }

    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->content)) / 180));
    }

    public function formattedContent(): string
    {
        if (! preg_match('/<\/?[a-z][^>]*>/i', $this->content)) {
            return nl2br(e($this->content));
        }

        return BlogContentSanitizer::sanitize($this->content);
    }
}
