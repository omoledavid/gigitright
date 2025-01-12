<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExperienceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Experience',
            'id' => $this->id,
            'attributes' => [
                'job_title' => $this->job_title,
                'company_name' => $this->company_name,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'location' => $this->location,
                'description' => $this->description,
                'user_id' => $this->user_id,
                'status' => resourceStatus($this->status),
            ]
        ];
    }
}
