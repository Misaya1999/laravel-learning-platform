<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category';

    protected $fillable = [
        'name',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }
}
