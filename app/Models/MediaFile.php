<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaFile extends Model
{
    protected $guarded = ['id'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
