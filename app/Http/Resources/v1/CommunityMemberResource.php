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
                    'profile_image' => $this->user->profile->profile_image ? url(getFilePath('user_profile').'/'.$this->user->profile->profile_image) : null,
                    'created_at' => $this->created_at->toDateTimeString(),
                    'updated_at' => $this->updated_at->toDateTimeString(),
                ]
            ];
    }
}
