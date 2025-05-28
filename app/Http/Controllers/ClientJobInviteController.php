<?php

namespace App\Http\Controllers;

use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Http\Resources\JobInviteResource;
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
            return $this->error('You are not authorized to delete this job invite', 403);
        }
        if ($jobInvite->status === 'accepted') {
            $jobInvite->update(['status' => 'canceled']);
            $milestones = Milestone::where('job_id', $jobInvite->job->id)
                ->where('user_id', $jobInvite->talent_id)
                ->get();
            
            if ($milestones->isNotEmpty()) {
                $milestones->each->delete();
            }

            $hasAcceptedInvite = JobInvite::where('job_id', $jobInvite->job->id)
                ->where('status', 'accepted')
                ->exists();

            if (!$hasAcceptedInvite) {
                $jobInvite->job->update(['status' => 'open']);
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
            return $this->error('You cannot cancel an accepted job invite', 422);
        }
        if ($jobInvite->status === 'canceled') {
            return $this->error('This job invite has already been canceled', 422);
        }
        $jobInvite->update(['status' => 'canceled']);
        return $this->ok('Job Invite canceled successfully');
    }
}
