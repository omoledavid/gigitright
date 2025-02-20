<?php

namespace App\Services\PaymentGateways;

use Illuminate\Support\Facades\Http;

class FlutterwaveService
{
    protected $flutterwaveUrl;
    protected $secretKey;

    public function __construct()
    {
        $this->flutterwaveUrl = env('FLUTTERWAVE_PAYMENT_URL', 'https://api.flutterwave.com/v3');
        $this->secretKey = env('FLUTTERWAVE_SECRET_KEY');
    }

    public function initiatePayment($user, $amount, $callbackUrl)
    {
        $response = Http::withToken($this->secretKey)->post("{$this->flutterwaveUrl}/payments", [
            'tx_ref' => uniqid(),
            'amount' => $amount,
            'currency' => 'NGN',
            'meta' => [
                'user_id' => $user->id,
                'callback_url' => $callbackUrl,
            ],
            'redirect_url' => env('APP_URL').'/api/v1/payment/verify/flutterwave',
            'customer' => [
                'email' => $user->email,
            ],
        ]);

        return $response->json()['data']['link'] ?? null;
    }

    public function verifyPayment($reference)
    {
        $response = Http::withToken($this->secretKey)->get("{$this->flutterwaveUrl}/transactions/{$reference}/verify");

        if ($response->successful() && $response->json()['data']['status'] === 'successful') {
            return [
                'status' => 'success',
                'amount' => $response->json()['data']['amount'],
                'metadata' => $response->json()['data'],
            ];
        }

        return ['status' => 'failed', 'response' => $response->json()];
    }
}
