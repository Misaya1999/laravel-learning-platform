<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'password',
        'phone',
        'avatar',
        'email_verified_at',
        'terms_version',
        'terms_accepted_at',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->role === 'admin' || $this->email_verified_at !== null;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification((string) $token));
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot(['status', 'enrolled_at', 'expires_at', 'completed_at'])
            ->withTimestamps();
    }

    public function courseReviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function lessonViews(): HasMany
    {
        return $this->hasMany(LessonView::class);
    }

    public function announcementReads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function blogComments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }

    public function blogRatings(): HasMany
    {
        return $this->hasMany(BlogRating::class);
    }
}
