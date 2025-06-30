<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\JobApplicantStatus;
use App\Enums\JobStatus;
use App\Enums\NotificationType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TransactionSource;
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
use Illuminate\Support\Facades\DB;

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

        // Get platform job charge percent from settings
        $jobChargePercent = (float) gs('job_charge');
        $platformCharge = 0;
        if ($jobChargePercent > 0) {
            $platformCharge = ($validatedData['budget'] * $jobChargePercent) / 100;
        }

        $totalDebit = $validatedData['budget'] + $platformCharge;

        if ($user->bal < $totalDebit) {
            return $this->error('You do not have enough money to create this job');
        }

        $job = Job::create($validatedData);

        $user->wallet->withdraw($totalDebit);
        $user->escrow_wallet->deposit($validatedData['budget']);

        createTransaction(
            userId: $user->id,
            transactionType: TransactionType::DEBIT,
            amount: $totalDebit,
            description: 'Funds put away for Job (including platform charge)'
        );

        // Record platform charge if any
        if ($platformCharge > 0) {
            createPlatformTransaction(
                amount: $platformCharge,
                source: TransactionSource::JOB,
                type: TransactionType::DEBIT,
                status: PaymentStatus::PENDING,
                model: $job,
                note: 'Platform charge for job creation'
            );
        }

        return $this->ok('success', new JobResource($job));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $job = Job::query()->where('id', $id)->with(['applicants.applicant', 'relatedJobs', 'applicants.applicant.reviews', 'milestones', 'milestones.talent'])->firstOrFail();
        return $this->ok('success', [
            'job' => new JobResource($job),
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

        $job = Job::findOrFail($validatedData['job_id']);
        $user = auth()->user();

        // Check resume and cover letter
        $resume = $user->profile->resume ?? null;
        $coverLetter = $user->profile->cover_letter ?? null;

        if (is_null($resume) && is_null($coverLetter)) {
            return $this->error('Kindly update your resume and cover letter');
        }

        // Prevent duplicate application
        $alreadyApplied = JobApplicants::where('user_id', $user->id)
            ->where('job_id', $job->id)
            ->exists();

        if ($alreadyApplied) {
            return $this->error("You already applied for this job");
        }

        // Prevent applying to own job
        if ($job->user_id === $user->id) {
            return $this->error("You cannot apply for your own job");
        }

        // Ensure job is open
        if ($job->status !== JobStatus::OPEN) {
            return $this->error("This job is not open for applications");
        }

        // Check Griftis balance
        if (optional($user->griftis)->balance <= 0) {
            return $this->error('You do not have enough Griftis to apply for this job');
        }

        // If milestones exist, validate total budget
        if (!empty($validatedData['milestones'])) {
            $milestoneTotal = array_sum(array_column($validatedData['milestones'], 'amount'));
            if ($milestoneTotal > $job->budget) {
                return $this->error('Total milestone amount cannot be greater than the job budget');
            }
        }

        // Begin transaction
        DB::beginTransaction();

        try {
            // Debit Griftis for job application
            $applyCharge = gs('job_apply_charge');
            $user->griftis->withdraw($applyCharge);
            // Record platform charge for job application
            createPlatformTransaction(
                $applyCharge,
                TransactionSource::JOB_APPLICATION,
                'charge',
                'completed',
                $user,
                "Job application fee for job GFT{$job->id}"
            );

            // Create transaction record
            createTransaction(
                $user->id,
                TransactionType::DEBIT,
                $applyCharge,
                "Job application fee for job GFT{$applyCharge}",
                PaymentMethod::GFT,
                currency: 'GFT',
                status: PaymentStatus::COMPLETED
            );
            // Create job application
            $jobApplicant = JobApplicants::create([
                'job_id' => $job->id,
                'user_id' => $user->id,
                'resume' => $resume,
                'cover_letter' => $coverLetter,
                'status' => JobApplicantStatus::PENDING,
            ]);

            // Create milestones
            if (!empty($validatedData['milestones'])) {
                foreach ($validatedData['milestones'] as $milestone) {
                    Milestone::create([
                        'user_id'    => $user->id,
                        'job_id'     => $job->id,
                        'title'      => $milestone['title'],
                        'description' => $milestone['description'] ?? null,
                        'amount'     => $milestone['amount'],
                        'due_date'   => $milestone['date'],
                    ]);
                }
            }

            // Create notification for job owner
            createNotification($job->user_id, NotificationType::JOB_APPLICATION, [
                'title'   => 'Job Application',
                'message' => "You have a new job application",
                'url'     => '',
                'id'      => $jobApplicant->id,
            ]);

            DB::commit();

            return $this->ok('Application submitted successfully', new JobApplicantResource($jobApplicant));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('An error occurred while submitting your application. Please try again later.'.$e);
        }
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
