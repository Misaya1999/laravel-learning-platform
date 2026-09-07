<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'enrolled_at',
        'expires_at',
        'expiry_reminder_sent_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'expires_at' => 'datetime',
            'expiry_reminder_sent_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Enrollment $enrollment) {
            $enrollment->enrolled_at ??= now();
            $enrollment->expires_at ??= $enrollment->course
                ->calculateExpiresAt($enrollment->enrolled_at);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function remainingDays(): ?int
    {
        if ($this->expires_at === null) {
            return null;
        }

        $seconds = $this->expires_at->getTimestamp() - now()->getTimestamp();

        return max(0, (int) ceil($seconds / 86400));
    }
}
