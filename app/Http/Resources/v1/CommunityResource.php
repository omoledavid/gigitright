<?php

namespace App\Http\Resources\v1;

use App\Enums\CommunityStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Community',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'status' => ($this->is_private === CommunityStatus::PUBLIC) ? 'public' : 'private',
                'total_members' => $this->members_count,
                'created_at' => $this->created_at,
                'updated_at' => $this->cover_image,
            ],
            'relationships' => [
                'created_by' => new UserResource($this->whenLoaded('creator')),
                'posts' => $this->whenLoaded('posts')
            ]
        ];
    }
}
