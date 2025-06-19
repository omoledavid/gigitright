<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformTransaction extends Model
{
    protected $fillable = [
        'amount',
        'source',
        'type',
        'status',
        'model_type',
        'model_id',
        'user_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'string',
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
