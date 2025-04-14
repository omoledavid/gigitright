<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Post',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'content' => $this->content,
                'image' => $this->image,
                'status' => $this->status,
                'post_likes' => count($this->likes),
                'post_comments' => count($this->comments),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'author' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'user_title' => $this->user->profile->user_title,
                'profile_image' => $this->user->profile->profile_image ? url(getFilePath('user_profile').'/'.$this->user->profile?->profile_image) : null,
            ],
            'relationships' => [
                'community' => new CommunityResource($this->whenLoaded('community')),
                'post_comments' => PostCommentResource::collection($this->whenLoaded('comments')),

            ]
        ];
    }
}
