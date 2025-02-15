<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MannikJ\Laravel\Wallet\Traits\HasWallet;

class Account extends Model
{
    use HasFactory, HasWallet;
    protected $guarded = [];
}
