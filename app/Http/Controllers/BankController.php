<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class BankController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bankAccounts = BankAccount::query()->where('user_id', auth()->id())->latest()->get();
        return $this->ok('Bank accounts retrieved successfully', BankAccountResource::collection($bankAccounts));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'bank_name' => 'required',
            'account_number' => 'required',
            'bank_code' => 'required',
            'account_name' => 'required',
        ]);
        $alreadyExist = BankAccount::query()->where('bank_code', $validatedData['bank_code'])->where('user_id', auth()->id())->first();
        if ($alreadyExist) {
            return $this->error("Bank account already exist", 409);
        }
        $validatedData['user_id'] = auth()->id();
        $existingBankAccount = BankAccount::query()->where('user_id', auth()->id())->first();
        if(!$existingBankAccount) {
            $validatedData['is_default'] = true;
        }
        $bankAccount = BankAccount::query()->create($validatedData);
        return $this->ok('Bank account created successfully', BankAccountResource::make($bankAccount), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bankAccount = BankAccount::query()->where('id',$id)->first();
        if(!$bankAccount) {
            return $this->error("Bank account not found", 404);
        }
        $bankAccount->delete();
        return $this->ok('Bank account deleted successfully', 200);
    }
}
