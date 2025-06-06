<?php

namespace App\Http\Controllers;

use App\Enums\NotificationType;
use App\Enums\TransactionSource;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\JobInviteResource;
use App\Http\Resources\v1\JobResource;
use App\Http\Resources\v1\MilestoneResource;
use App\Http\Resources\v1\UserResource;
use App\Models\JobApplicants;
use App\Models\JobInvite;
use App\Models\Milestone;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ClientJobInviteController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invites = JobInvite::query()->where('client_id', auth()->user()->id)
            ->with(['talent', 'client', 'job'])
            ->latest()
            ->get();
        return $this->success('Job Invites fetched successfully', JobInviteResource::collection($invites));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:job_applicants,id',
            'message' => 'nullable|string',
        ]);
        $jobApplicant = JobApplicants::query()->where('id', $request->application_id)->first();
        $existInvite = JobInvite::query()->where('application_id', $request->application_id)->first();
        if ($existInvite) {
            return $this->error('You have already invited this applicant', 422);
        }
        $applicant = $jobApplicant->applicant;
        $jobInvite = JobInvite::create([
            'application_id' => $request->application_id,
            'client_id' => auth()->user()->id,
            'talent_id' => $applicant->id,
        ]);
        $notifyMsg = [
            'title' => 'Job Invite',
            'message' => "You have been invited to a job",
            'url' => '',
            'id' => $jobInvite->job->id
        ];
        createNotification($applicant->id, NotificationType::JOB_INVITE, $notifyMsg);
        return $this->ok('Job Invite sent successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $jobInvite = JobInvite::query()->where('id', $id)->with(['talent', 'job'])->firstOrFail();
        if ($jobInvite->client_id !== auth()->user()->id) {
            return $this->error('You are not authorized to view this job invite', 403);
        }
        return $this->ok('Job Invite fetched successfully', new JobInviteResource($jobInvite));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jobInvite = JobInvite::query()->where('id', $id)->firstOrFail();
        if ($jobInvite->client_id !== auth()->user()->id) {
            return $this->error('You are not authorized to cancel this job invite', 403);
        }
        if ($jobInvite->status === 'accepted') {
            $jobInvite->update(['status' => 'canceled']);
            $milestones = Milestone::where('job_id', $jobInvite->job->id)
                ->where('user_id', $jobInvite->talent_id)
                ->get();

            if ($milestones->isNotEmpty()) {
                // Only delete unpaid milestones
                $milestones->where('is_paid', false)->each->delete();
            }

            $hasAcceptedInvite = JobInvite::whereHas('job', function ($query) use ($jobInvite) {
                $query->where('jobs.id', $jobInvite->job->id);
            })
                ->where('status', 'accepted')
                ->exists();

            if (!$hasAcceptedInvite) {
                $jobInvite->job->update(['status' => 'open']);
            }

            // Calculate remaining budget by subtracting paid milestone amounts
            $paidMilestoneAmount = $milestones->where('is_paid', true)->sum('amount');
            $remainingBudget = $jobInvite->job->budget - $paidMilestoneAmount;

            // Only transfer remaining budget if there is any
            if ($remainingBudget > 0) {
                $jobInvite->talent->escrow_wallet->withdraw($remainingBudget);
                createTransaction(
                    userId: $jobInvite->talent->id,
                    transactionType: TransactionType::DEBIT,
                    amount: $remainingBudget,
                    description: 'Fund Transfer to client Escrow',
                    source: TransactionSource::ESCROW,
                    status: TransactionStatus::COMPLETED
                );
                $jobInvite->client->escrow_wallet->deposit($remainingBudget);
                createTransaction(
                    userId: $jobInvite->client->id,
                    transactionType: TransactionType::CREDIT,
                    amount: $remainingBudget,
                    description: 'Fund Transfer from Talent Escrow',
                    source: TransactionSource::ESCROW,
                    status: TransactionStatus::COMPLETED
                );
            }

            $notifyMsg = [
                'title' => 'Contract terminated',
                'message' => "Your contract with {$jobInvite->client->name} has been terminated",
                'url' => '',
                'id' => $jobInvite->job->id
            ];
            createNotification($jobInvite->talent_id, NotificationType::JOB_INVITE, $notifyMsg);
            $clientNotifyMsg = [
                'title' => 'Contract terminated',
                'message' => "You have terminated your contract with {$jobInvite->talent->name}",
                'url' => '',
                'id' => $jobInvite->job->id
            ];
            createNotification($jobInvite->client_id, NotificationType::JOB_INVITE, $clientNotifyMsg);
            return $this->ok('Contract terminated successfully', [
                'job' => new JobResource($jobInvite->job),
                'milestones' => MilestoneResource::collection($milestones),
                'talent' => new UserResource($jobInvite->talent),
                'client' => new UserResource($jobInvite->client),
                'remaining_budget' => $remainingBudget,
                'paid_milestone_amount' => $paidMilestoneAmount
            ]);
        }
        if ($jobInvite->status === 'canceled') {
            return $this->error('This job invite has already been canceled', 422);
        }
        $jobInvite->update(['status' => 'canceled']);
        return $this->ok('Job Invite canceled successfully');
    }
}
