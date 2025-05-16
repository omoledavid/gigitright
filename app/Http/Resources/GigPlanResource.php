<?php

namespace App\Http\Resources;

use App\Http\Resources\v1\GigResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'gig plan',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'price' => $this->price,
                'features' => $this->features,
                'created_at' => $this->created_at->toDateTimeString(),
            ],
            'relationships' => [
                'gig' => new GigResource($this->whenLoaded('gig')),
            ]
        ];
    }
}
