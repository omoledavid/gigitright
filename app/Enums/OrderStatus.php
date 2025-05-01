<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case DELIVERED = 'delivered';
    case REFUNDED = 'refunded';

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }
    public function isInProgress(): bool
    {
        return $this === self::IN_PROGRESS;
    }
}
