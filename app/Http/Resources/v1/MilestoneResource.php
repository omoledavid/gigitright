<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MilestoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'milestones',
            'id' => $this->id,
            'attributes' => [
                'job_id' => $this->job_id,
                'title' => $this->title,
                'description' => $this->description,
                'amount' => $this->amount,
                'due_date' => $this->due_date,
                'status' => $this->status, // pending, in_progress, completed, cancelled
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at->toDateTimeString(),
            ]
        ];
    }
}
