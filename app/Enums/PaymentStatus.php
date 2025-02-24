<?php

namespace App\Enums;

enum PaymentStatus: string
{
    const PENDING = 'pending';
    const COMPLETED = 'completed';
    const FAILED = 'failed';
    const REFUNDED = 'refunded';
}
