<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'wishlist',
            'id' => $this->id,
            'attributes' => [
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ],
                'job' => new JobResource($this->job),
                'added_at' => $this->created_at->diffForHumans(),
            ]
        ];
    }
}
