<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Enums\PaymentStatus;
use App\Enums\TransactionSource;
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
        if ($transaction->transaction_source === TransactionSource::WALLET && $transaction->transaction_type === TransactionType::CREDIT) {
            $transaction->update(['status' => PaymentStatus::REFUNDED]);
            $transaction->user->wallet->deposit($transaction->amount);
            return $this->ok('Wallet refund', new TransactionResource($transaction));
        }
        return $this->error('Wallet refund failed');
    }

    public function setRates(Request $request)
    {
        $request->validate([
            'griftis_to_naira' => 'required|numeric',
            'buyer_percentage' => 'nullable|numeric|min:0|max:100',
            'seller_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        // Assuming we store these values in a settings table
        set_setting('griftis_to_naira', $request->griftis_to_naira);
        //        set_setting('buyer_percentage', $request->buyer_percentage);
        //        set_setting('seller_percentage', $request->seller_percentage);

        return $this->ok('Rates updated successfully');
    }

    public function getFinancialMetrics()
    {
        // Calculate total inflow (all credit transactions)
        $totalInflow = Transaction::where('transaction_type', TransactionType::CREDIT)
            ->where('status', PaymentStatus::COMPLETED)
            ->sum('amount');

        // Calculate total outflow (all debit transactions)
        $totalOutflow = Transaction::where('transaction_type', TransactionType::DEBIT)
            ->where('status', PaymentStatus::COMPLETED)
            ->sum('amount');

        // Calculate profit (inflow - outflow)
        $profit = $totalInflow - $totalOutflow;

        // Get transaction counts
        $totalTransactions = Transaction::count();
        $completedTransactions = Transaction::where('status', PaymentStatus::COMPLETED)->count();
        $pendingTransactions = Transaction::where('status', PaymentStatus::PENDING)->count();
        $refundedTransactions = Transaction::where('status', PaymentStatus::REFUNDED)->count();

        return $this->ok('Financial metrics', [
            'total_inflow' => $totalInflow,
            'total_outflow' => $totalOutflow,
            'profit' => $profit,
            'transaction_stats' => [
                'total_transactions' => $totalTransactions,
                'completed_transactions' => $completedTransactions,
                'pending_transactions' => $pendingTransactions,
                'refunded_transactions' => $refundedTransactions
            ]
        ]);
    }
}
