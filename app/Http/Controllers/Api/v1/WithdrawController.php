<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\NotificationType;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\WithdrawalResource;
use App\Models\Withdraw;
use App\Models\AccountDetail;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class WithdrawController extends Controller
{
    use ApiResponses;
    /**
     * Request a new withdrawal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10', // Minimum withdrawal amount
            'bank_account_id' => 'required|exists:bank_accounts,id',
        ]);

        $user = Auth::user();

        // Check if the user has enough balance (Assuming a balance column in users table)
        try {
            if ($user->balance < $validated['amount']) {
                return $this->error('Insufficient balance');
            }

            // Verify bank account exists and belongs to user
            $accountDetail = AccountDetail::where('id', $validated['bank_account_id'])
                ->where('user_id', $user->id)
                ->first();

            if (!$accountDetail) {
                return $this->error('Invalid bank account', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Deduct balance and create transaction
            $user->wallet->withdraw($validated['amount']);
            createTransaction(
                userId: $user->id,
                transactionType: TransactionType::DEBIT,
                amount: $validated['amount'],
                description: 'Withdrawal request',
                source: TransactionSource::WALLET
            );

            // Create withdrawal request
            $withdrawal = Withdraw::create([
                'user_id' => $user->id,
                'account_detail_id' => $accountDetail->id,
                'amount' => $validated['amount'],
                'status' => WithdrawalStatus::PENDING,
            ]);

            $notifyMsg = [
                'title' => 'Withdrawal Request',
                'message' => "Your withdrawal request of {$validated['amount']} has been sent successfully",
                'url' => '',
                'id' => $withdrawal->id
            ];
            createNotification($user->id, NotificationType::WITHDRAWAL_REQUESTED, $notifyMsg);
        } catch (\Exception $e) {
            // Reverse the withdrawal if it was made
            if (isset($withdrawal)) {
                $user->wallet->deposit($validated['amount']);
                createTransaction(
                    userId: $user->id,
                    transactionType: TransactionType::CREDIT,
                    amount: $validated['amount'],
                    description: 'Withdrawal reversal due to error',
                    source: TransactionSource::WALLET
                );
                $withdrawal->delete();
            }
            throw $e;
        }

        return $this->ok('Withdraw request sent', new WithdrawalResource($withdrawal), Response::HTTP_CREATED);
    }

    /**
     * Get user's withdrawal history
     */
    public function index()
    {
        $withdrawals = Withdraw::where('user_id', Auth::id())->latest()->get();
        return $this->ok('Withdrawals', WithdrawalResource::collection($withdrawals), Response::HTTP_OK);
    }

    /**
     * View a specific withdrawal
     */
    public function show($id)
    {
        $withdrawal = Withdraw::where('user_id', Auth::id())->find($id);

        if (!$withdrawal) {
            return $this->error('Withdrawal not found', Response::HTTP_NOT_FOUND);
        }

        return $this->ok('success', new WithdrawalResource($withdrawal));
    }
}
