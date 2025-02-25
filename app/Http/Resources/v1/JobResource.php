<?php

namespace App\Http\Resources\v1;

use App\Models\JobApplicants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'job',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'description' => $this->description,
                'category_id' => $this->category_id,
                'sub_category_id' => $this->sub_category_id,
                'budget' => $this->budget,
                'duration' => $this->duration,
                'job_type' => $this->job_type,
                'deadline' => $this->deadline,
                'visibility' => $this->visibility,
                'location' => $this->location,
                'skill_requirements' => $this->skill_requirements,
                'no_applicants' => count($this->applicants),
                'status' => $this->status,
                'created_at' => $this->created_at->diffForHumans(),
            ],
            'relationships' => [
                'applicants' => JobApplicantResource::collection($this->whenLoaded('applicants')),
                'milestones' => MilestoneResource::collection($this->whenLoaded('milestones')),
            ]
        ];
    }
}
