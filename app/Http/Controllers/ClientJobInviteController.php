<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobInviteResource;
use App\Models\JobApplicants;
use App\Models\JobInvite;
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
        return $this->ok('Job Invite sent successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
            return $this->error('You cannot cancel an accepted job invite', 422);
        }
        if ($jobInvite->status === 'canceled') {
            return $this->error('This job invite has already been canceled', 422);
        }
        $jobInvite->update(['status' => 'canceled']);
        return $this->ok('Job Invite canceled successfully');
    }
}
