<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $guarded = ['id'];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
