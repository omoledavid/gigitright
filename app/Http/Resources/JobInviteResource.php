<?php

namespace App\Http\Resources;

use App\Http\Resources\v1\JobResource;
use App\Http\Resources\v1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobInviteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'job_invite',
            'id' => $this->id,
            'attributes' => [
                'status' => $this->status,
                'message' => $this->message,
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at->toDateTimeString(),
            ],
            'client' => new UserResource($this->whenLoaded('client')),
            'talent' => new UserResource($this->whenLoaded('talent')),
            'job' => new JobResource($this->whenLoaded('job')),
        ];
    }
}
