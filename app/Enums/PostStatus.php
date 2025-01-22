<?php

namespace App\Enums;

enum PostStatus: string
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
}
