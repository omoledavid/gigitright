<?php

namespace App\Http\Resources;

use App\Http\Resources\v1\GigResource;
use App\Http\Resources\v1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Order',
            'id' => $this->id,
            'attributes' => [
                'status' => $this->status,
                'start_date' => $this->start_date ? $this->start_date->toDateTimeString() : null,
                'end_date' => $this->end_date ? $this->end_date->toDateTimeString() : null,
                'amount' => $this->amount,
                'plan_name' => $this->plan_name,
                'client_mark_as_complete' => $this->client_mark_as_complete,
                'talent_mark_as_complete' => $this->talent_mark_as_complete,
                'delivered_at' => $this->delivered_at ? $this->delivered_at->toDateTimeString() : null,
                'due_date' => $this->due_date ? $this->due_date->toDateTimeString() : null,
            ],
            'links' => [
                // 'self' => route('orders.show', $this->id),
            ],
            'relationships' => [
                'client' => new UserResource($this->whenLoaded('client')),
                'talent' => new UserResource($this->whenLoaded('talent')),
                'gig' => new GigResource($this->whenLoaded('gig')),
            ],
        ];
    }
}
