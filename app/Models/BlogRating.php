<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogRating extends Model
{
    protected $fillable = ['blog_post_id', 'user_id', 'rating'];

    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
