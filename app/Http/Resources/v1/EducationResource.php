<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Education',
            'id' => $this->id,
            'attributes' => [
                'degree' => $this->degree,
                'field_of_study' => $this->field_of_study,
                'institution_name' => $this->institution_name,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'location' => $this->location,
                'grade' => $this->grade,
                'description' => $this->description,
                'is_ongoing' => $this->is_ongoing,
                'status' => resourceStatus($this->status),
                'user_id' => $this->user_id,
            ]
        ];
    }
}
