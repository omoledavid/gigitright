<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'support_ticket',
            'id' => (string) $this->id,
            'attributes' => [
                'subject' => $this->subject,
                'priority' => $this->priority,
                'status' => $this->status,
                'created_at' => $this->created_at->toIso8601String(),
                'updated_at' => $this->updated_at->toIso8601String(),
            ],
            'relationships' => [
                'user' => new UserResource($this->whenLoaded('user')),
                'latest_message' => new TicketMessageResource($this->whenLoaded('latestMessage')),
                'messages' => TicketMessageResource::collection($this->whenLoaded('messages')),
            ],
        ];
    }
}
