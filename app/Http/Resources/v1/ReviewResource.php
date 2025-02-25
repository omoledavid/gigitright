<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Review',
            'id' => $this->id,
            'attributes' => [
                'reviewer' => [
                    'id' => $this->reviewer->id,
                    'name' => $this->reviewer->name,
                    'profile_image' => $this->reviewer->profile->profile_image,
                ],
                'reviewee' => [
                    'id' => $this->reviewee->id,
                    'name' => $this->reviewee->name,
                    'profile_image' => $this->reviewer->profile->profile_image,
                ],
                'rating' => $this->rating,
                'review' => $this->review,
                'created_at' => $this->created_at->diffForHumans(),
            ]
        ];
    }
}
