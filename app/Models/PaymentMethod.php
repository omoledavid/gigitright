<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Integer;

class PaymentMethod extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'transaction_fee' => 'decimal:2'
    ];
}
