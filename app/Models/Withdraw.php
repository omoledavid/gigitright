<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_detail_id',
        'amount',
        'status',
        'admin_note'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function account() {
        return $this->belongsTo(AccountDetail::class, 'account_detail_id');
    }
}
