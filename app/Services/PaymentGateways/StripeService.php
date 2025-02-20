<?php

namespace App\Services\PaymentGateways;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeService
{
    public function initiatePayment($user, $amount)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Deposit'],
                    'unit_amount' => $amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => env('STRIPE_SUCCESS_URL'),
            'cancel_url' => env('STRIPE_CANCEL_URL'),
        ]);

        return $session->url;
    }

    public function verifyPayment($reference)
    {
        // Handle Stripe webhook verification separately
        return ['status' => 'pending'];
    }
}
