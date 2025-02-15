<?php

namespace App\Enums;

enum UserStatus: int
{
    const INACTIVE = 0;
    const ACTIVE = 1;
    const BLOCKED = 2;
}
