<?php

namespace App\Enums;

enum TransactionStatus: string
{
    const COMPLETED = 'completed';
    const REFUNDED = 'refunded';
}
