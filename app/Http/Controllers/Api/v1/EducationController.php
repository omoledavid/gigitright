<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\EducationResource;
use App\Models\Education;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $education = $user->education;
        return $this->ok('success', EducationResource::collection($education));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'degree' => 'required',
            'field_of_study' => 'required',
            'institution_name' => 'required',
            'start_date' => 'required|date',
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('is_ongoing') && $value) {
                        $fail('End date must be null if the education is ongoing.');
                    }
                },
            ],
            'location' => 'required',
            'grade' => 'required',
            'description' => 'required',
            'is_ongoing' => 'sometimes|boolean',
        ]);

        $validatedData['user_id'] = auth()->id();
        $validatedData['status'] = Status::ACTIVE;
        $education = Education::create($validatedData);
        return $this->ok('education created successfully', new EducationResource($education));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $education = Education::query()->findOrFail($id);
        return $this->ok('education retrieved successfully', new EducationResource($education));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'degree' => 'required',
            'field_of_study' => 'required',
            'institution_name' => 'required',
            'start_date' => 'required|date',
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('is_ongoing') && $value) {
                        $fail('End date must be null if the education is ongoing.');
                    }
                },
            ],
            'location' => 'required',
            'grade' => 'required',
            'description' => 'required',
            'is_ongoing' => 'sometimes|boolean',
        ]);
        $education = Education::query()->findOrFail($id);
        $education->update($validatedData);
        return $this->ok('education updated successfully', new EducationResource($education));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $education = Education::query()->findOrFail($id);
        $education->delete();
        return $this->ok('education deleted successfully');
    }
}
