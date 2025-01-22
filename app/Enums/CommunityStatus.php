<?php

namespace App\Enums;

enum CommunityStatus: int
{
    const PUBLIC = 0;
    const PRIVATE = 1;
    const CLOSED = 2;
}
