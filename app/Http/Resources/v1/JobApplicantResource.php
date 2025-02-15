<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'JobApplicant',
            'id' => $this->id,
            'attributes' => [
                'job_id' => $this->job_id,
                'user_id' => $this->user_id,
                'name' => $this->applicants->name,
                'email' => $this->applicants->email,
                'country' => $this->applicants->country,
                'resume' => $this->resume,
                'cover_letter' => $this->cover_letter,
                'status' => ($this->status === 0) ? 'pending' : (($this->status === 1) ? 'accepted' : 'rejected'),
                'updated_at' => $this->updated_at,
                'created_at' => $this->created_at,
            ],
            'relationships' => [
                'job' => $this->job,
            ]
        ];
    }
}
