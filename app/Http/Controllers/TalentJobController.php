<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\JobResource;
use App\Models\Job;
use App\Models\JobInvite;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class TalentJobController extends Controller
{
    use ApiResponses;
    
    public function onGoingJobs()
    {
        $jobInvite = JobInvite::query()
            ->where('talent_id', auth()->id())
            ->where('status',  'accepted')
            ->with(['job', 'client'])
            ->orderBy('created_at', 'desc')
            ->latest()
            ->get();
            $jobs = $jobInvite->pluck('job');
        return $this->ok('success', JobResource::collection($jobs));
    }
    public function viewOnGoingJob($id)
    {
        $job = Job::query()->where('id', $id)->with(['applicants', 'relatedJobs'])->first();
        if (!$job) {
            return $this->error('Job not found', 404);
        }
        return $this->ok('success', new JobResource($job));
    }
}
