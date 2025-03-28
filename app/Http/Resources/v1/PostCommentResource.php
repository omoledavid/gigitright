<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'comment',
            'id' => $this->id,
            'attributes' => [
                'comment' => $this->comment,
            ],
            'user' => [
                'id' => $this->id,
                'name' => $this->postUser->name,
                'profile_image' => $this->postUser->profile->profile_image ?? null,
                'email' => $this->postUser->email
            ]
        ];
    }
}
