<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'file',
            'id' => $this->id,
            'attributes' => [
                'file_path' => url(getFilePath('messaging').'/'.$this->file_path),
                'file_type' => $this->file_type,
                'original_name' => $this->original_name,
                'created_at' => $this->created_at,
                'uploaded_at' => $this->uploaded_at,
            ]
        ];
    }
}
