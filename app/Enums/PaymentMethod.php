<?php

namespace App\Enums;

enum PaymentMethod: string
{
    const WALLET = 'wallet';
    const PAYSTACK = 'paystack';
    const FLUTTERWAVE = 'flutterwave';
    const GFT = 'gft';
}
