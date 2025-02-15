<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = ['id'];
    public function markAsRead()
    {
        $this->is_read = true;
        $this->save();
        return $this;
    }
}
