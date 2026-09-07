<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'course_id', 'course_name', 'price'];
    protected function casts(): array { return ['price' => 'decimal:2']; }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}
