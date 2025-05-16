<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'bank_name',
        'bank_code',
        'account_name',
        'account_number',
        'user_id',
        'is_default',
    ];
}
