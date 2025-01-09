<?php

namespace App\Enums;

enum UserRole: string
{
    const FREELANCER = 'freelancer';
    const CLIENT = 'client';
    const ADMIN = 'administrator';
}
