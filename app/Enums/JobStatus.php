<?php

namespace App\Enums;

enum JobStatus: int
{
    const PENDING = 0;
    const ACCEPTED = 1;
    const REJECTED = 2;
}
