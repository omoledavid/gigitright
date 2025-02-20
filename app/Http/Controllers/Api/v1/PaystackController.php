<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;

class PaystackController extends Controller
{
// Step 1: Initialize Payment
    public function initializePayment(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'amount' => 'required|integer|min:100', // Amount in kobo (100 kobo = ₦1)
        ]);

        $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))->post(env('PAYSTACK_PAYMENT_URL') . '/transaction/initialize', [
            'email' => $user->email,
            'amount' => $request->amount * 100, // Convert Naira to Kobo
            'callback_url' => env('APP_URL') . '/api/v1/paystack/verify/' // Callback for verification
        ]);

        $data = $response->json();

        if ($response->failed()) {
            return response()->json(['message' => 'Payment initialization failed', 'error' => $data], 400);
        }

        return response()->json([
            'message' => 'Payment initialized',
            'authorization_url' => $data['data']['authorization_url'],
            'reference' => $data['data']['reference']
        ], 200);
    }

// Step 2: Verify Payment
    public function verifyPayment(Request $request)
    {
        $reference = $request->query('reference'); // Get reference from query string
        if (!$reference) {
            return response()->json(['message' => 'Reference is required'], 400);
        }

        $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))->get(env('PAYSTACK_PAYMENT_URL') . "/transaction/verify/{$reference}");

        $data = $response->json();

        if ($data['status'] && $data['data']['status'] === 'success') {
//            Transaction::create([
//                'email' => $data['data']['customer']['email'],
//                'amount' => $data['data']['amount'] / 100,
//                'reference' => $reference,
//                'status' => 'successful'
//            ]);

            return response()->json(['message' => 'Payment verified successfully', 'payment' => $data['data']], 200);
        }

        return response()->json(['message' => 'Payment verification failed'], 400);
    }

}
