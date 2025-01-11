<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ['type' => 'portfolio',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'description' => $this->description,
                'image' => $this->image ? url(getFilePath('portfolio').'/'.$this->image) : null,
                'link' => $this->link,
                'technologies' => $this->technologies,
                'date' => $this->date,
                'status' => ($this->status === 0) ? 'inactive' : 'active',
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
//                $this->mergeWhen($request->routeIs('users.*'), [
//                        'emailVerifiedAt' => $this->email_verified_at,
//                        'updatedAt' => $this->updated_at
//
//                    ]
//                )
            ]
        ];
    }
}
