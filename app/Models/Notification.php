<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
    ];
    public function markAsRead()
    {
        $this->is_read = true;
        $this->save();
        return $this;
    }
}
