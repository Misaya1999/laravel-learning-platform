<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = ['user_id', 'code', 'status', 'subtotal', 'total', 'payment_method', 'payos_order_code', 'payment_link_id', 'checkout_url', 'qr_code', 'terms_version', 'terms_accepted_at', 'transaction_id', 'receipt_path', 'receipt_submitted_at', 'confirmed_by', 'paid_at'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'total' => 'decimal:2', 'terms_accepted_at' => 'datetime', 'receipt_submitted_at' => 'datetime', 'paid_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function confirmedBy(): BelongsTo { return $this->belongsTo(User::class, 'confirmed_by'); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
}
