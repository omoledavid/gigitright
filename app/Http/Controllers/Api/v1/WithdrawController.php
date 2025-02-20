<?php

namespace App\Http\Controllers\Api\v1;

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
    public function store(Request $request) {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10', // Minimum withdrawal amount
            'account_detail_id' => 'required|exists:account_details,id',
        ]);

        $user = Auth::user();

        // Check if the user has enough balance (Assuming a balance column in users table)
        if ($user->balance < $validated['amount']) {
            return $this->error('Insufficient balance');
        }

        // Deduct balance (Consider implementing a transaction for safety)
        $user->wallet->withdraw($validated['amount']);

        // Create withdrawal request
        $withdrawal = Withdraw::create([
            'user_id' => $user->id,
            'account_detail_id' => $validated['account_detail_id'],
            'amount' => $validated['amount'],
            'status' => WithdrawalStatus::PENDING,
        ]);

        return $this->ok('Withdraw request sent',new WithdrawalResource($withdrawal), Response::HTTP_CREATED);
    }

    /**
     * Get user's withdrawal history
     */
    public function index() {
        $withdrawals = Withdraw::where('user_id', Auth::id())->latest()->get();
        return $this->ok('Withdrawals',WithdrawalResource::collection($withdrawals), Response::HTTP_OK);
    }

    /**
     * View a specific withdrawal
     */
    public function show($id) {
        $withdrawal = Withdraw::where('user_id', Auth::id())->find($id);

        if (!$withdrawal) {
            return $this->error('Withdrawal not found', Response::HTTP_NOT_FOUND);
        }

        return $this->ok('success', new WithdrawalResource($withdrawal));
    }
}

