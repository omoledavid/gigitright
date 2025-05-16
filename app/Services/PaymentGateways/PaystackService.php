<?php

namespace App\Services\PaymentGateways;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    protected $paystackUrl;
    protected $secretKey;

    public function __construct()
    {
        $this->paystackUrl = env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co');
        $this->secretKey = env('PAYSTACK_SECRET_KEY');
    }

    public function initiatePayment($user, $amount, $callbackUrl)
    {
        $response = Http::withToken($this->secretKey)->post("{$this->paystackUrl}/transaction/initialize", [
            'email' => $user->email,
            'amount' => $amount * 100, // Paystack accepts amount in kobo
            'metadata' => [
                'user_id' => $user->id,
                'callback_url' => $callbackUrl,
            ],
            'callback_url' => env('APP_URL').'/api/v1/payment/verify/paystack',
        ]);

        return $response->json()['data']['authorization_url'] ?? null;
    }

    public function verifyPayment($reference)
    {
        $response = Http::withToken($this->secretKey)->get("{$this->paystackUrl}/transaction/verify/{$reference}");

        if ($response->successful() && $response->json()['data']['status'] === 'success') {
            return [
                'status' => 'success',
                'amount' => $response->json()['data']['amount'] / 100,
                'metadata' => $response->json()['data']['metadata'],
            ];
        }

        return ['status' => 'failed'];
    }
    public function validateBankAccount($accountNumber, $bankCode)
    {
        $url = "https://api.paystack.co/bank/resolve";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Cache-Control' => 'no-cache',
        ])->get($url, [
            'account_number' => $accountNumber,
            'bank_code' => $bankCode,
        ]);

        if ($response->failed()) {
            return [
                'error' => true,
                'message' => "HTTP Error: " . $response->body(),
            ];
        }

        return [
            'error' => false,
            'data' => $response->json(),
        ];
    }
}
