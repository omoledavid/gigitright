<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\JobApplicantStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Filters\v1\JobFilter;
use App\Http\Resources\v1\JobApplicantResource;
use App\Http\Resources\v1\JobResource;
use App\Http\Resources\v1\UserResource;
use App\Models\Job;
use App\Models\JobApplicants;
use App\Models\Milestone;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class JobController extends Controller
{
    use ApiResponses;

    /**
     * Display a listing of the resource.
     */
    public function index(JobFilter $filter)
    {
        $jobs = Job::query()->where('user_id', auth()->id())->filter($filter)->orderBy('created_at', 'desc')->latest()->get();
        return $this->ok('success', JobResource::collection($jobs));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
            'budget' => 'required|numeric|min:0',
            'duration' => 'nullable|string|max:50',
            'skill_requirements' => 'nullable|array',
            'skill_requirements.*' => 'string|max:100', // Validate each skill if it's an array
            'attachments' => 'nullable|array',
            'attachments.*' => 'url|max:2048', // Validate each attachment URL
            'job_type' => 'required|in:fixed,hourly',
            'status' => 'nullable|in:open,in_progress,completed,cancelled',
            'deadline' => 'nullable|date|after:today',
            'visibility' => 'required|in:public,private',
            'location' => 'nullable|string|max:255',
        ]);
        $validatedData['user_id'] = auth()->id();
        if ($user->bal < $validatedData['budget']) {
            return $this->error('You do not have enough gft to apply for this job');
        }
        $job = Job::create($validatedData);
        $user->wallet->withdraw($validatedData['budget']);
        $user->escrow_wallet->deposit($validatedData['budget']);
        createTransaction(userId: $user->id, transactionType: TransactionType::DEBIT, amount: $validatedData['budget'], description: 'Funds put away for Job');
        return $this->ok('success', new JobResource($job));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $job = Job::query()->findOrFail($id);
        return $this->ok('success', [
            'job' => new JobResource($job),
            'related_jobs' => JobResource::collection($job->relatedJobs)
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:categories,id',
            'budget' => 'required|numeric|min:0',
            'duration' => 'nullable|string|max:50',
            'skill_requirements' => 'nullable|array',
            'skill_requirements.*' => 'string|max:100', // Validate each skill if it's an array
            'attachments' => 'nullable|array',
            'attachments.*' => 'url|max:2048', // Validate each attachment URL
            'job_type' => 'required|in:fixed,hourly',
            'status' => 'nullable|in:open,in_progress,completed,cancelled',
            'deadline' => 'nullable|date|after:today',
            'visibility' => 'required|in:public,private',
            'location' => 'nullable|string|max:255',
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
        $job = Job::query()->findOrFail($validatedData['job_id']);
        $data = new UserResource(auth()->user());
        $user = $data->toArray(request());
        $resume = $user['relationships']['profile']['resume'] ?? null;
        $coverLetter = $user['relationships']['profile']['cover_letter'] ?? null;
        if (is_null($resume) && is_null($coverLetter)) {
            return $this->error('Kindly update your resume and cover letter');
        }
        $validatedData['user_id'] = $user['id'];
        $validatedData['resume'] = $resume;
        $validatedData['cover_letter'] = $coverLetter;
        $validatedData['status'] = JobApplicantStatus::PENDING;
        $exist = JobApplicants::query()->where('user_id', $validatedData['user_id'])->where('job_id', $validatedData['job_id'])->first();
        if (!empty($exist)) {
            return $this->error("You already applied for this job");
        }
        if (!empty($validatedData['milestones'])) {
            $milestoneTotalAmount = array_sum(array_column($validatedData['milestones'], 'amount'));

            if ($milestoneTotalAmount > $job->budget) {
                return $this->error('Total milestone amount cannot be greater than budget');
            }

            foreach ($validatedData['milestones'] as $milestone) {
                Milestone::create([
                    'user_id'    => $user['id'], // Make sure $user is an array or use $user->id
                    'job_id'     => $validatedData['job_id'],
                    'title'      => $milestone['title'],
                    'description'=> $milestone['description'] ?? null,
                    'amount'     => $milestone['amount'],
                    'due_date'   => $milestone['date'],
                ]);
            }
        }

        $jobApplicants = JobApplicants::query()->create($validatedData);
        return $this->ok('success', new JobApplicantResource($jobApplicants));
    }

    public function viewJobApplication($id)
    {
        $jobApplication = JobApplicants::query()->findOrFail($id);
        return $this->ok('success', new JobApplicantResource($jobApplication));
    }

    public function allJobs(JobFilter $filter)
    {
        return $this->ok('success', JobResource::collection(Job::filter($filter)->latest()->get()));
    }
}
