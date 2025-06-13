<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\DepositStatus;
use App\Enums\NotificationType;
use App\Enums\PaymentStatus;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Http\Filters\v1\TransactionFilter;
use App\Http\Resources\v1\TransactionResource;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Services\PaymentGateways\PaystackService;
use App\Services\PaymentGateways\FlutterwaveService;
use App\Services\PaymentGateways\StripeService;

class DepositController extends Controller
{
    use ApiResponses;
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
        $notifyMsg = [
            'title' => 'Deposit Initiated',
            'message' => "A deposit of {$amount} has been initiated via {$gateway}",
            'url' => null
        ];
        createNotification($user->id, NotificationType::DEPOSIT_INITIATED, $notifyMsg);

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
        if (empty($request->reference)) {
            $ref = $request->transaction_id;
        } else {
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
            createTransaction(userId: $user->id, transactionType: TransactionType::CREDIT, amount: $paymentStatus['amount'], paymentMethod: $gateway, status: PaymentStatus::COMPLETED);
            $notifyMsg = [
                'title' => 'Deposit Completed',
                'message' => "Your deposit of {$paymentStatus['amount']} has been completed successfully",
                'url' => null
            ];
            createNotification($user->id, NotificationType::DEPOSIT_COMPLETED, $notifyMsg);

            //return response()->json(['message' => 'Payment verified successfully'], 200);
            return redirect()->away($callbackURL ?? 'https://gigitright.com/dashboard/wallet');
        }

        return response()->json(['message' => 'Payment verification failed', 'error' => $paymentStatus], 400);
    }
    public function buyGriftis(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:10',
        ]);
        $rate = gs('gft_rate');
        $griftis = $request->amount * $rate;
        //pay for grifts
        $user = auth()->user();
        //check balance
        if ($user->balance < $griftis) {
            return $this->error('Insufficient Balance');
        }
        try {
            $user->wallet->withdraw($griftis);
            createTransaction(userId: $user->id, transactionType: TransactionType::DEBIT, amount: $griftis, status: PaymentStatus::COMPLETED);
            $user->griftis->deposit($validatedData['amount']);
            createTransaction(userId: $user->id, transactionType: TransactionType::CREDIT, amount: $validatedData['amount'], currency: 'GFT', status: PaymentStatus::COMPLETED,);
            $notifyMsg = [
                'title' => 'Griftis Purchase Successful',
                'message' => "You have successfully purchased {$validatedData['amount']} Griftis",
                'url' => null
            ];
            createNotification($user->id, NotificationType::GRIFTIS_PURCHASED, $notifyMsg);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
        return $this->ok('Deposit of griftis was successfully', new UserResource($user));
    }
    public function transactions(TransactionFilter $filter)
    {
        $user = auth()->user();
        return TransactionResource::collection($user->transactions()->filter($filter)->latest()->get());
    }
}
