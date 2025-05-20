<?php

namespace App\Enums;

enum NotificationType: string
{
    const GIG_CREATED = 'gig_created';
    const GIG_UPDATED = 'gig_updated';
    const GIG_DELETED = 'gig_deleted';
}
