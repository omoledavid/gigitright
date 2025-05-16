<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\GigPlanResource;
use App\Models\GigPlan;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class GigPlanController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $gigPlans = GigPlan::whereHas('gig', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        return $this->ok('Gig plans retrieved successfully.', GigPlanResource::collection($gigPlans->load('gig')));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'gig_id' => 'required|exists:gigs,id',
            'name' => 'required|string|max:255',
            'price' => 'required|integer',
            'features' => 'nullable|array'
        ]);
        $gigPlan = GigPlan::create($validatedData);

        return $this->ok('Gig plan created successfully.', new GigPlanResource($gigPlan));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $gigPlan = GigPlan::where('id', $id)
            ->whereHas('gig', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->firstOrFail();

        return $this->ok('Gig plan retrieved successfully.', new GigPlanResource($gigPlan->load('gig')));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        $gigPlan = GigPlan::where('id', $id)
            ->whereHas('gig', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->firstOrFail();

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|integer',
            'features' => 'nullable|array'
        ]);

        $gigPlan->update($validatedData);

        return $this->ok('Gig plan updated successfully.', new GigPlanResource($gigPlan->load('gig')));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        $gigPlan = GigPlan::where('id', $id)
            ->whereHas('gig', function ($query) use ($user) {
            $query->where('user_id', $user->id);
            })
            ->first();

        if (!$gigPlan) {
            return $this->error('Gig plan not found.', 404);
        }

        $gigPlan->delete();

        return $this->ok('Gig plan deleted successfully.');
    }
}
