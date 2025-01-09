<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'skills' => 'array',
        'languages' => 'array'
    ];
}
