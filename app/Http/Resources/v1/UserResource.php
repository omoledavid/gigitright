<?php

namespace App\Http\Resources\v1;

use App\Enums\OrderStatus;
use App\Http\Resources\ProfileResource;
use App\Models\JobApplicants;
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
                'email_verified' => !(($this->ev === 0)),
                'sms_verified' => !(($this->sv === 0)),
                'status' => $this->status,
                'role' => $this->role,
                'gigs_in_progress' => $this->orders()->where('status', OrderStatus::IN_PROGRESS)->count(),
                'completed_gigs' => $this->orders()->where('status', OrderStatus::COMPLETED)->count(),
                'profile_image' => $this->profile?->profile_image ? url(getFilePath('user_profile').'/'.$this->profile?->profile_image) : null,
                $this->mergeWhen($request->routeIs('users.*'), [
                        'emailVerifiedAt' => $this->email_verified_at,
                        'updatedAt' => $this->updated_at

                    ]
                )
            ],
            'links' => '',
            'Accounts' => [
                'main_wallet' => $this->wallet?->balance,
                'escrow_wallet' => $this->escrow_wallet?->balance,
                'griftis' => $this->griftis?->balance
            ],
            'relationships' => [
//                'author' => [
//                    'data' => [
//                        'type' => 'user',
//                        'id' => $this->id
//                    ],
//                    'links' => 'later'
//                ]
                'profile' => new ProfileResource($this->whenLoaded('profile')),
                'portfolios' => PortfolioResource::collection($this->whenLoaded('portfolio')),
                'certifications' => CertificateResource::collection($this->whenLoaded('certificate')),
                'experiences' => ExperienceResource::collection($this->whenLoaded('experience')),
                'education' => EducationResource::collection($this->whenLoaded('education')),
                'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
                'jobs_applied_for' => JobApplicantResource::collection($this->whenLoaded('jobApplications')),
            ],
        ];
    }
}
