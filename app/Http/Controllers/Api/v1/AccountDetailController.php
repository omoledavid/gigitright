<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AccountDetailResource;
use App\Models\AccountDetail;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AccountDetailController extends Controller
{
    use ApiResponses;
    /**
     * Store user's bank details for withdrawals
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50|unique:account_details,account_number',
            'account_name' => 'required|string|max:255',
            'swift_code' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:10',
        ]);

        $validated['user_id'] = Auth::id(); // Attach to logged-in user

        $account = AccountDetail::create($validated);

        return $this->ok('Bank details saved successfully.', new AccountDetailResource($account), Response::HTTP_CREATED);
    }

    /**
     * Get the user's saved bank details
     */
    public function show() {
        $account = AccountDetail::where('user_id', Auth::id())->first();

        if (!$account) {
            return $this->error('Account not found', Response::HTTP_NOT_FOUND);
        }
        return $this->ok('Bank details retrieved successfully.', new AccountDetailResource($account), Response::HTTP_OK);
    }

    /**
     * Update the user's bank details
     */
    public function update(Request $request) {
        $account = AccountDetail::where('user_id', Auth::id())->first();

        if (!$account) {
            return $this->error('Account not found', Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'bank_name' => 'sometimes|string|max:255',
            'account_number' => 'sometimes|string|max:50|unique:account_details,account_number,' . $account->id,
            'account_name' => 'sometimes|string|max:255',
            'swift_code' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:10',
        ]);

        $account->update($validated);
        return $this->ok('Bank details updated successfully.', new AccountDetailResource($account), Response::HTTP_OK);
    }

    /**
     * Delete the user's bank details
     */
    public function destroy() {
        $account = AccountDetail::where('user_id', Auth::id())->first();

        if (!$account) {
            return $this->error('No bank details found', 404,Response::HTTP_NOT_FOUND);
        }

        $account->delete();

        return $this->ok('Bank details deleted successfully.', Response::HTTP_OK);
    }
}

