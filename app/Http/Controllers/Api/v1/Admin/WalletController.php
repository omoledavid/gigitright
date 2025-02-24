<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\TransactionResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Models\Transaction;

class WalletController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $totalRevenue = Transaction::sum('amount');
        $totalDeposits = Transaction::where('transaction_type', TransactionType::CREDIT)->sum('amount');
        $totalWithdrawals = Transaction::where('transaction_type', TransactionType::DEBIT)->sum('amount');

        $transactions = Transaction::latest()->get(); // List of transactions
        return $this->ok('Wallet info', [
            'total_revenue' => $totalRevenue,
            'total_deposits' => $totalDeposits,
            'total_withdrawals' => $totalWithdrawals,
            'transactions' => TransactionResource::collection($transactions)
        ]);
    }

    public function show($id)
    {
        return $this->ok('Wallet Transaction', new TransactionResource(Transaction::findOrFail($id)));
    }

    public function refund(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== PaymentStatus::COMPLETED) {
            return $this->error('Only completed transactions are allowed');
        }

        // Process the refund logic (integrate payment gateway refund if applicable)
        $transaction->update(['status' => PaymentStatus::REFUNDED]);

        return response()->json(['message' => 'Transaction refunded successfully']);
    }

    public function setRates(Request $request)
    {
        $request->validate([
            'griftis_to_dollars' => 'required|numeric',
            'buyer_percentage' => 'required|numeric|min:0|max:100',
            'seller_percentage' => 'required|numeric|min:0|max:100',
        ]);

        // Assuming we store these values in a settings table
        setting(['griftis_to_dollars' => $request->griftis_to_dollars]);
        setting(['buyer_percentage' => $request->buyer_percentage]);
        setting(['seller_percentage' => $request->seller_percentage]);
        setting()->save();

        return response()->json(['message' => 'Rates updated successfully']);
    }
}

