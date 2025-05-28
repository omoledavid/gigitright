<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'certificate',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'description' => $this->description,
                'organization' => $this->organization,
                'date_awarded' => $this->date_awarded,
                'valid_until' => $this->valid_until,
                'certificate_file' => $this->certificate_file ? url(getFilePath('certificates').'/'.$this->certificate_file) : null,
                'user_id' => $this->user_id,
                'status' => ($this->status == 0) ? 'Inactive' : 'Active',
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,

            ]
        ];
    }
}
