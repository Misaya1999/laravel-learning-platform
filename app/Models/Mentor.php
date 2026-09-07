<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mentor extends Model
{
    protected $fillable = [
        'name',
        'email',
        'specialty',
        'avatar',
        'bio',
        'philosophy',
        'credentials',
        'is_main',
    ];

    protected function casts(): array
    {
        return ['is_main' => 'boolean'];
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_mentor');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(MentorCertificate::class)->orderBy('position')->orderBy('id');
    }
}
