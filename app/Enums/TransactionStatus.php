<?php

namespace App\Enums;

enum TransactionStatus: string
{
    const COMPLETED = 'completed';
    const PENDING = 'pending';
    const REFUNDED = 'refunded';
}
