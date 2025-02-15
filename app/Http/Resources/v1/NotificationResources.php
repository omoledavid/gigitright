<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'notification',
            'id' => $this->id,
            'attributes' => [
                'type' => $this->type,
                'data' => $this->data,
                'is_read' => !(($this->is_read === 0)),
                'created_at' => $this->created_at?->diffForHumans(),
                'updated_at' => $this->updated_at?->diffForHumans(),
            ]
        ];
    }
}
