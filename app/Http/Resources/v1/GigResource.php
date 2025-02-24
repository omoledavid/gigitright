<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
        'type' => 'Gig',
        'id' => $this->id,
        'attributes' => [
            'title' => $this->title,
            'description' => $this->description,
            'skills' => $this->skills, // Assuming it's stored as an array (JSON column)
            'location' => $this->location,
            'previous_works_companies' => $this->previous_works_companies, // Array
            'language' => $this->language,
            'unique_selling_point' => $this->unique_selling_point,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ],
        'relationships' => [
            'plans' => json_decode($this->plans, true)
//            'created_by' => new UserResource($this->user), // Assuming a `creator` relation exists
//            'plans' => GigPlanResource::collection($this->plans), // Assuming a `plans` relation
        ]
    ];
    }
}
