<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Message',
            'id' => $this->id,
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
            ],
            'message' => $this->message,
            'media_files' => MediaFileResource::collection($this->mediaFiles),
            'is_read' => $this->read,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
