<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Transaction',
            'id' => $this->id,
            'attributes' => [
                'user_id' => $this->user_id,
                'transaction_type' => $this->transaction_type, // credit or debit
                'amount' => $this->amount,
                'currency' => $this->currency,
                'description' => $this->description ?? '',
                'status' => $this->status, // pending, completed, failed, refunded
                'payment_method' => $this->payment_method, // e.g., card, bank transfer
                'transaction_source' => $this->transaction_source, // wallet, purchase
                'reference' => $this->reference, // Unique transaction reference
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at->toDateTimeString(),
            ]
        ];
    }
}
