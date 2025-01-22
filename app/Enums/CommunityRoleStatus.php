<?php

namespace App\Enums;

enum CommunityRoleStatus: string
{
    const MEMBER = 'member';
    const MODERATOR = 'moderator';
    const ADMIN = 'admin';
}
