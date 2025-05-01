<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'user_id',
        'gig_id',
        'value',
        'min_order_value',
        'expires_at',
        'is_active',
        'usage_limit',
        'used_count'
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'used_count' => 'integer',
        'usage_limit' => 'integer',
        'min_order_value' => 'decimal:2',
        'value' => 'decimal:2',
    ];

    protected $dates = ['expires_at'];

    public function isValid($orderTotal)
    {
        return $this->is_active
            && ($this->usage_limit === null || $this->used_count < $this->usage_limit)
            && ($this->expires_at === null || $this->expires_at->isFuture())
            && ($this->min_order_value === null || $orderTotal >= $this->min_order_value);
    }

    public function applyDiscount($orderTotal)
    {
        if ($this->type === 'fixed') {
            return max(0, $orderTotal - $this->value);
        }

        if ($this->type === 'percent') {
            return max(0, $orderTotal - ($orderTotal * ($this->value / 100)));
        }

        return $orderTotal;
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }
}
