<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $guarded = ['id'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
