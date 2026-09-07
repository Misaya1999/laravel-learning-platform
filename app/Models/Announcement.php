<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = ['title', 'content', 'type', 'is_published', 'published_at'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'published_at' => 'datetime'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }
}
