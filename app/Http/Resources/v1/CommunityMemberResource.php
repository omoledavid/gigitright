<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
            [
                'type' => 'CommunityMember',
                'id' => $this->id,
                'attributes' => [
                    'name' => $this->user->name,
                    'role' => $this->role,
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at,
                ]
            ];
    }
}
