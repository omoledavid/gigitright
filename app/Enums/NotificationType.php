<?php

namespace App\Enums;

enum NotificationType: string
{
    const GIG_CREATED = 'gig_created';
    const GIG_UPDATED = 'gig_updated';
    const GIG_DELETED = 'gig_deleted';
    case ACCOUNT_STATUS_CHANGED = 'account_status_changed';
    case DEPOSIT_INITIATED = 'deposit_initiated';
    case DEPOSIT_COMPLETED = 'deposit_completed';
    case ORDER_COMPLETED = 'order_completed';
    case PORTFOLIO_ADDED = 'portfolio_added';
    case WITHDRAWAL_REQUESTED = 'withdrawal_requested';
    case BANK_ACCOUNT_ADDED = 'bank_account_added';
    case COUPON_CREATED = 'coupon_created';
    case COUPON_APPLIED = 'coupon_applied';
    case JOB_INVITE = 'job_invite';
    case JOB_INVITE_ACCEPTED = 'job_invite_accepted';
    case JOB_INVITE_REJECTED = 'job_invite_rejected';
    case ORDER_ACCEPTED = 'order_accepted';
    case ORDER_REJECTED = 'order_rejected';
    case ORDER_CREATED = 'order_created';
    case JOB_APPLICATION = 'job_application';
    case GRIFTIS_PURCHASED = 'griftis_purchase';
    case JOB_DELETED = 'job_deleted';
    case JOB_CREATED = 'job_created';
}
