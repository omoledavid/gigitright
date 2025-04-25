<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\JobApplicantResource;
use App\Models\Job;
use App\Models\JobApplicants;
use App\Traits\ApiResponses;

class ManageClientController extends Controller
{
    use ApiResponses;
    public function jobApplications()
    {
        $applicants = JobApplicants::query()->whereHas('job', function ($query) {
            $query->where('user_id', auth()->id());
        })->with(['job', 'applicant'])->latest()->get();
        return $this->ok('Applicants Retreived successfully', JobApplicantResource::collection($applicants));
    }
}
