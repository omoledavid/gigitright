<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = [
        'title',
        'user_id',
        'description',
        'link',
        'technologies',
        'date',
        'status',
        'image'
    ];
    protected $casts = [
        'technologies' => 'array',
        'date' => 'date'
    ];
}
