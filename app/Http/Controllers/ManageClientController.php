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
    public function viewApplication($id)
    {
        $applicant = JobApplicants::query()->where('id', $id)->with(['job', 'applicant'])->first();
        if(!$applicant) {
            return $this->error('Applicant not found', 404);
        }
        if($applicant->job->user_id != auth()->id()) {
            return $this->error('You are not authorized to view this applicant', 403);
        }
        return $this->ok('Applicant Retreived successfully', new JobApplicantResource($applicant));
    }
}
