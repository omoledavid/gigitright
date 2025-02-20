<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\DepositStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Services\PaymentGateways\PaystackService;
use App\Services\PaymentGateways\FlutterwaveService;
use App\Services\PaymentGateways\StripeService;

class DepositController extends Controller
{
    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'gateway' => 'required|in:paystack,flutterwave,stripe',
            'callback_url' => 'required|url',
        ]);

        $user = auth()->user();
        $amount = $request->amount;
        $gateway = $request->gateway;
        $callbackUrl = $request->callback_url;

        switch ($gateway) {
            case 'paystack':
                $service = new PaystackService();
                break;
            case 'flutterwave':
                $service = new FlutterwaveService();
                break;
            case 'stripe':
                $service = new StripeService();
                break;
            default:
                return response()->json(['message' => 'Invalid payment gateway'], 400);
        }

        $paymentUrl = $service->initiatePayment($user, $amount, $callbackUrl);

        return response()->json([
            'message' => 'Payment link generated',
            'payment_url' => $paymentUrl
        ], 201);
    }

    public function verify(Request $request, $gateway)
    {
        $request->validate([
            'reference' => 'nullable|string',
            'tx_ref' => 'nullable|string'
        ]);
        if(empty($request->reference)) {
            $ref = $request->transaction_id;
        }else{
            $ref = $request->reference;
        }
        $reference = $ref;

        switch ($gateway) {
            case 'paystack':
                $service = new PaystackService();
                break;
            case 'flutterwave':
                $service = new FlutterwaveService();
                break;
            case 'stripe':
                $service = new StripeService();
                break;
            default:
                return response()->json(['message' => 'Invalid payment gateway'], 400);
        }

        $paymentStatus = $service->verifyPayment($reference);

        if ($paymentStatus['status'] === 'success') {
            $userId = $paymentStatus['metadata']['meta']['user_id'] ?? $paymentStatus['metadata']['user_id'];
            $callbackURL = $paymentStatus['metadata']['meta']['callback_url'] ?? $paymentStatus['metadata']['callback_url'];
            $user = User::query()->find($userId);
            Deposit::create([
                'user_id' => $paymentStatus['metadata']['meta']['user_id'] ?? $paymentStatus['metadata']['user_id'],
                'amount' => $paymentStatus['amount'],
                'gateway' => $gateway,
                'reference' => $reference,
                'status' => DepositStatus::SUCCESS,
            ]);
            //Deposit money to wallet
            $user->wallet->deposit($paymentStatus['amount']);

            //return response()->json(['message' => 'Payment verified successfully'], 200);
            return redirect($callbackURL);
        }

        return response()->json(['message' => 'Payment verification failed', 'error' => $paymentStatus], 400);
    }
    public function buyGriftis(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);
    }
}
