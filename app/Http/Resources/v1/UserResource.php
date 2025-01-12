<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'user',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'email' => $this->email,
                'email_verified' => ($this->ev === 0) ? 'Unverified' : 'Verified',
                'sms_verified' => ($this->sv === 0) ? 'Unverified' : 'Verified',
                'role' => $this->role,
                $this->mergeWhen($request->routeIs('users.*'), [
                        'emailVerifiedAt' => $this->email_verified_at,
                        'updatedAt' => $this->updated_at

                    ]
                )
            ],
            'relationships' => [
//                'author' => [
//                    'data' => [
//                        'type' => 'user',
//                        'id' => $this->id
//                    ],
//                    'links' => 'later'
//                ]
                'profile' => $this->profile,
                'portfolios' => $this->portfolio,
                'certifications' => $this->certificate,
                'experiences' => $this->experience,
                'education' => $this->education
            ]
        ];
    }
}
