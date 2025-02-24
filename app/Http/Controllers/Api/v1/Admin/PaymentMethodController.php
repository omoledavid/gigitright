<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Enums\PaymentMethodStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\Admin\PaymentMethodResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    use ApiResponses;
    /**
     * Get all payment methods.
     */
    public function index()
    {
        return $this->ok('Payment methods', PaymentMethodResource::collection(PaymentMethod::all()));
    }

    /**
     * Store or update a payment method.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:payment_methods,name',
            'slug' => 'required|string|max:255|unique:payment_methods,slug',
            'transaction_fee' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
        ]);
        $validatedData['status'] = PaymentMethodStatus::ACTIVE;

        $paymentMethod = PaymentMethod::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment method added successfully',
            'data' => $paymentMethod
        ]);
    }

    /**
     * Update an existing payment method.
     */
    public function update(Request $request, $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255|unique:payment_methods,name,' . $id,
            'slug' => 'sometimes|string|max:255|unique:payment_methods,slug,' . $id,
            'status' => 'sometimes|in:active,inactive',
            'transaction_fee' => 'nullable|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
        ]);

        $paymentMethod->update(array_filter($validatedData, fn($value) => !is_null($value)));

        return $this->ok('Payment method updated successfully',new PaymentMethodResource($paymentMethod));
    }
}

