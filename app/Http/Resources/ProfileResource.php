<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'profile',
            'id' => $this->id,
            'attributes' => [
                'user_title' => $this->user_title,
                'skills' => $this->skills,
                'pay_rate' => $this->pay_rate,
                'languages' => $this->languages,
                'resume' => $this->resume ? url(getFilePath('resume').'/'.$this->resume) : null,
                'cover_letter' => $this->cover_letter,
                'location' => $this->location,
                'profile_image' => $this->profile_image ? url(getFilePath('user_profile').'/'.$this->profile_image) : null,
                'bio' => $this->bio,
                'extra_info' => $this->extra_info,
                'created_at' => $this->created_at->toDateTimeString(),
            ]
        ];
    }
}
