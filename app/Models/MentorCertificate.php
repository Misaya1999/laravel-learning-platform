<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorCertificate extends Model
{
    protected $fillable = ['mentor_id', 'image', 'position'];

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Mentor::class);
    }
}
