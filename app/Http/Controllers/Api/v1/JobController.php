<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\JobApplicantResource;
use App\Http\Resources\v1\JobResource;
use App\Http\Resources\v1\UserResource;
use App\Models\Job;
use App\Models\JobApplicants;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class JobController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $jobs = $user->jobs;
        return $this->ok('success', JobResource::collection($jobs));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'required|string',
            'category_id'         => 'required|exists:categories,id',
            'sub_category_id'     => 'nullable|exists:categories,id',
            'budget'              => 'required|numeric|min:0',
            'duration'            => 'nullable|string|max:50',
            'skill_requirements'  => 'nullable|array',
            'skill_requirements.*'=> 'string|max:100', // Validate each skill if it's an array
            'attachments'         => 'nullable|array',
            'attachments.*'       => 'url|max:2048', // Validate each attachment URL
            'job_type'            => 'required|in:fixed,hourly',
            'status'              => 'nullable|in:open,in_progress,completed,cancelled',
            'deadline'            => 'nullable|date|after:today',
            'visibility'          => 'required|in:public,private',
            'location'=> 'nullable|string|max:255',
        ]);
        $validatedData['user_id'] = auth()->id();
        $job = Job::create($validatedData);
        return $this->ok('success', new JobResource($job));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $job = Job::query()->findOrFail($id);
        return $this->ok('success', new JobResource($job));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'required|string',
            'category_id'         => 'required|exists:categories,id',
            'sub_category_id'     => 'nullable|exists:categories,id',
            'budget'              => 'required|numeric|min:0',
            'duration'            => 'nullable|string|max:50',
            'skill_requirements'  => 'nullable|array',
            'skill_requirements.*'=> 'string|max:100', // Validate each skill if it's an array
            'attachments'         => 'nullable|array',
            'attachments.*'       => 'url|max:2048', // Validate each attachment URL
            'job_type'            => 'required|in:fixed,hourly',
            'status'              => 'nullable|in:open,in_progress,completed,cancelled',
            'deadline'            => 'nullable|date|after:today',
            'visibility'          => 'required|in:public,private',
            'location'=> 'nullable|string|max:255',
        ]);
        $job = Job::query()->findOrFail($id);
        $job->update($validatedData);
        return $this->ok('success', new JobResource($job));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $job = Job::query()->findOrFail($id);
        $job->delete();
        return $this->ok('successfully deleted');
    }

    public function jobApplication(Request $request)
    {
        $validatedData = $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'milestones' => 'nullable|array',
        ]);
        $data = new UserResource(auth()->user());
        $user = $data->toArray(request());
        $resume = $user['relationships']['profile']['resume'];
        $coverLetter = $user['relationships']['profile']['cover_letter'];
        $validatedData['user_id'] = $user['id'];
        $validatedData['resume'] = $resume;
        $validatedData['cover_letter'] = $coverLetter;
        $validatedData['status'] = JobStatus::PENDING;
        if(!empty($validatedData['milestones'])){
//            $milestones = json_decode($validatedData['milestones']);
            $milestones = [];
            foreach ($validatedData['milestones'] as $milestone){
                $milestones[] = $milestone;
            }
        }
        $exist = JobApplicants::query()->where('user_id', $validatedData['user_id'])->where('job_id', $validatedData['job_id'])->first();
        if(!empty($exist)){
            return $this->error("You already applied for this job");
        }
        $jobApplicants = JobApplicants::query()->create($validatedData);
        return $this->ok('success', new JobApplicantResource($jobApplicants));
    }
    public function viewJobApplication($id)
    {
        $jobApplication = JobApplicants::query()->findOrFail($id);
        return $this->ok('success', new JobApplicantResource($jobApplication));
    }
}
