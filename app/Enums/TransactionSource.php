<?php

namespace App\Enums;

enum TransactionSource: string
{
    const WALLET = 'wallet';
    const GRIFTIS = 'griftis';
    const ESCROW = 'escrow';
    const JOB_APPLICATION = 'job_application';
    const JOB = 'job_creation';
}
