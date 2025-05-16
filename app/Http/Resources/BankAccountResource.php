<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'bank_account',
            'id' => $this->id,
            'attributes' => [
                'bank_name' => $this->bank_name,
                'account_name' => $this->account_name,
                'account_number' => $this->account_number,
                'created_at' => $this->created_at->toDateTimeString(),
            ]
        ];
    }
}
