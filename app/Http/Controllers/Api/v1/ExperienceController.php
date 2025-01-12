<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ExperienceResource;
use App\Models\Experience;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $experiences = $user->experience;
        return $this->ok('success', data: ExperienceResource::collection($experiences));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'job_title' => 'required',
            'company_name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required',
            'location' => 'required',
            'description' => 'nullable',
        ]);
        $validatedData['user_id'] = auth()->id();
        $validatedData['status'] = Status::ACTIVE;
        $experience = Experience::create($validatedData);
        return $this->ok('Experience created', data: new ExperienceResource($experience));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $experience = Experience::findOrFail($id);
        return $this->ok('Experience retrieved', data: new ExperienceResource($experience));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'job_title' => 'required',
            'company_name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required',
            'location' => 'required',
            'description' => 'nullable',
        ]);
        $experience = Experience::findOrFail($id);
        $experience->update($validatedData);
        return $this->ok('Experience update', data: new ExperienceResource($experience));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $experience = Experience::findOrFail($id);
        $experience->delete();
        return $this->ok('Experience deleted');
    }
}
