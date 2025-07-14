<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'ticket_message',
            'id' => (string) $this->id,
            'attributes' => [
                'message' => $this->message,
                'sender_id' => $this->sender_id,
                'sender_type' => $this->sender_type,
                'attachment' => $this->attachment ? asset('storage/' . $this->attachment) : null,
                'created_at' => $this->created_at->toIso8601String(),
                'updated_at' => $this->updated_at->toIso8601String(),
            ],
            'relationships' => [
                'support_ticket' => new SupportTicketResource($this->whenLoaded('supportTicket')),
                'sender' => new UserResource($this->whenLoaded('sender')),
            ],
        ];
    }
}
