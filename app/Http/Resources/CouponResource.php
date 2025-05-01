<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Coupon',
            'id' => $this->id,
            'attributes' => [
                'code' => $this->code,
                'type' => $this->type,
                'value' => $this->value,
                'min_order_value' => $this->min_order_value,
                'expires_at' => $this->expires_at ? $this->expires_at->toDateTimeString() : null,
                'is_active' => $this->is_active,
                'usage_limit' => $this->usage_limit,
                'used_count' => $this->used_count,
            ],
            'links' => [
                'self' => route('talent.coupon.show', $this->id),
            ],
        ];
    }
}
