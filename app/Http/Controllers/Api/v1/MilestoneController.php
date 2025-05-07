<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\MilestoneStatus;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\MilestoneResource;
use App\Models\Job;
use App\Models\Milestone;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MilestoneController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $request->validate([
            'job_id' => 'required|integer|exists:jobs,id',
        ]);
        $job = Job::query()->find($request->job_id);
        return $job->load('milestones');
    }

    public function store(Request $request)
    {
        $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|integer|min:1',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
        ]);

        $job = Job::findOrFail($request->job_id);

        // Set default status if not provided
        $status = $request->status ?? MilestoneStatus::PENDING;

        // Calculate the total milestone amount including the new one
        $totalMilestoneAmount = $job->milestones()->sum('amount') + $request->amount;

        if ($totalMilestoneAmount > $job->budget) {
            return $this->error('Total milestone amount cannot be greater than budget');
        }

        // Create milestone with the corrected data
        $milestone = Milestone::create([
            'job_id' => $request->job_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'status' => $status,
        ]);

        return $this->ok('Milestone created', new MilestoneResource($milestone), Response::HTTP_CREATED);
    }


    public function update(Request $request, Milestone $milestone)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'amount' => 'sometimes|integer|min:1',
            'due_date' => 'sometimes|date',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
        ]);

        $job = $milestone->job;

        // Recalculate total milestone amount if amount is being updated
        if ($request->has('amount')) {
            $totalMilestoneAmount = $job->milestones()
                    ->where('id', '!=', $milestone->id)
                    ->sum('amount') + $request->amount;

            if ($totalMilestoneAmount > $job->budget) {
                return $this->error('Total milestone amount cannot be greater than budget');
            }
        }

        // Ensure status is set to pending if not provided
        $data = $request->all();
        if (!$request->has('status')) {
            $data['status'] = MilestoneStatus::PENDING;
        }

        $milestone->update($data);

        return $this->ok('Milestone updated', new MilestoneResource($milestone), Response::HTTP_OK);
    }

    public function markAsCompleteByTalent($milestoneId)
    {
        $milestone = Milestone::query()->where('user_id', auth()->id())->find($milestoneId);
        if (!$milestone) {
            return $this->error('Milestone not found');
        }
        $milestone->update([
            'is_marked_complete_by_talent' => true,
        ]);
        return $this->ok('Milestone marked as complete', new MilestoneResource($milestone), Response::HTTP_OK);
    }

    public function markAsCompleteByClient($milestoneId)
    {
        $milestone = Milestone::query()->whereHas('job', function ($query) {
            $query->where('user_id', auth()->id());
        })->find($milestoneId);
        if (!$milestone) {
            return $this->error('Milestone not found');
        }
        $milestone->update([
            'is_marked_complete_by_client' => true,
        ]);
        if ($milestone->is_marked_complete_by_client === true && $milestone->is_marked_complete_by_talent === true) {
            $client = $milestone->client;
            $talent = $milestone->talent;
            try {
                DB::beginTransaction();
                $talent->escrow_wallet->withdraw($milestone->amount);
                createTransaction(userId: $talent->id, transactionType: TransactionType::DEBIT ,amount: $milestone->amount, description: 'Fund Transfer to talent', source: TransactionSource::ESCROW );
                $talent->wallet->deposit($milestone->amount);
                createTransaction(userId: $talent->id, transactionType: TransactionType::CREDIT ,amount: $milestone->amount, description: 'Fund received from Escrow', source: TransactionSource::WALLET );
                $milestone->update([
                    'is_paid' => true,
                ]);
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                return $this->error($th->getMessage());
            }
        }
        return $this->ok('Milestone completion confirmed', new MilestoneResource($milestone), Response::HTTP_OK);
    }

}
