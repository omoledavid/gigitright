<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobInviteResource;
use App\Models\JobInvite;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ManageTalentController extends Controller
{
    use ApiResponses;

    public function jobInvites()
    {
        $invites = JobInvite::query()->whereHas('talent', function ($query) {
            $query->where('user_id', auth()->id());
        })->with(['client', 'job'])->latest()->get();
        return $this->ok('Job Invites fetched successfully', JobInviteResource::collection($invites));
    }
    public function viewJobInvite($id)
    {;
        $invite = JobInvite::query()->where('id', $id)->with(['client', 'job'])->first();
        if (!$invite) {
            return $this->error('Job Invite not found', 404);
        }
        return $this->ok('Job Invite fetched successfully', new JobInviteResource($invite));
    }
    public function acceptInvite(Request $request)
    {
        $request->validate([
            'invite_id' => 'required|exists:job_invites,id',
        ]);
        $invite = JobInvite::query()->where('id', $request->invite_id)->first();
        if ($invite->status == 'canceled') {
            return $this->error('This invite has been cancelled', 422);
        }
        if ($invite->status == 'accepted') {
            return $this->error('You have already accepted this invite', 422);
        }
        $invite->update(['status' => 'accepted']);
        return $this->ok('Job Invite accepted successfully');
    }
    public function rejectInvite(Request $request)
    {
        $request->validate([
            'invite_id' => 'required|exists:job_invites,id',
        ]);
        $invite = JobInvite::query()->where('id', $request->invite_id)->first();
        if ($invite->status == 'canceled') {
            return $this->error('This invite has been cancelled', 422);
        }
        if ($invite->status == 'rejected') {
            return $this->error('You have already rejected this invite', 422);
        }
        if ($invite->status == 'accepted') {
            return $this->error('You have already accepted this invite', 422);
        }
        $invite->update(['status' => 'rejected']);
        return $this->ok('Job Invite rejected successfully');
    }
}
