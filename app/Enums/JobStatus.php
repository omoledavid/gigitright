<?php

namespace App\Enums;

enum JobStatus: string
{
    const OPEN = 'open';
    const IN_PROGRESS = 'in_progress';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';
}
