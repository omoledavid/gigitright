<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountDetail extends Model
{
    protected $fillable = [
        'user_id',
        'bank_name',
        'account_number',
        'account_name',
        'swift_code',
        'currency',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
