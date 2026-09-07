<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'course_section_id',
        'title',
        'type',
        'video_url',
        'video_path',
        'content',
        'attachment',
        'allow_attachment_download',
        'duration_minutes',
        'position',
        'is_preview',
    ];

    protected function casts(): array
    {
        return [
            'is_preview' => 'boolean',
            'allow_attachment_download' => 'boolean',
            'duration_minutes' => 'integer',
            'position' => 'integer',
        ];
    }

    public function section()
    {
        return $this->belongsTo(
            CourseSection::class,
            'course_section_id'
        );
    }

    public function videoEmbedUrl(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        $host = strtolower((string) parse_url($this->video_url, PHP_URL_HOST));
        $path = trim((string) parse_url($this->video_url, PHP_URL_PATH), '/');

        if (str_contains($host, 'youtu.be')) {
            return $path ? 'https://www.youtube-nocookie.com/embed/' . basename($path) : null;
        }

        if (str_contains($host, 'youtube.com')) {
            parse_str((string) parse_url($this->video_url, PHP_URL_QUERY), $query);
            $videoId = $query['v'] ?? (str_starts_with($path, 'embed/') ? basename($path) : null);
            return $videoId ? 'https://www.youtube-nocookie.com/embed/' . $videoId : null;
        }

        if (str_contains($host, 'vimeo.com') && basename($path)) {
            return 'https://player.vimeo.com/video/' . basename($path);
        }

        return null;
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}
