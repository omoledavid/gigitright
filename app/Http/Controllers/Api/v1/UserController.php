<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Filters\v1\UserFilter;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use ApiResponses;

    /**
     * Display a listing of the resource.
     */
    public function index(UserFilter $filter)
    {
        $userId = auth()->id();
        $user = User::query()->where('id', $userId)->filter($filter)->first();

        return $this->ok('success', new UserResource($user));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->ok('success', new UserResource($user));
    }


    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request)
    {
        $user = auth()->user();

        // Validate request data
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'user_title' => 'nullable|min:4',
            'skills' => 'nullable|array',
            'languages' => 'nullable|array',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'cover_letter' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'bio' => 'nullable|string|max:255',
            'pay_rate' => 'nullable|string|max:255',
            'extra_info' => 'nullable|string|max:255',
        ]);

        // Preserve existing data if no file is uploaded
        $existingProfile = $user->profile;

        // Handle file uploads
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile-images', 'public');
            $validatedData['profile_image'] = Storage::url($path);
        } else {
            $validatedData['profile_image'] = $existingProfile->profile_image ?? null;
        }

        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes', 'public');
            $validatedData['resume'] = Storage::url($path);
        } else {
            $validatedData['resume'] = $existingProfile->resume ?? null;
        }

        // Add user ID to data
        $validatedData['user_id'] = $user->id;

        // Update or create profile
        if ($existingProfile) {
            $existingProfile->update($validatedData);
        } else {
            $user->profile()->create($validatedData);
        }

        // Update user's name if provided
        if ($request->filled('name')) {
            $user->name = $request->name;
            $user->save();
        }

        // Return updated user data
        return $this->ok('User profile updated successfully', new UserResource($user));
    }

    public function switch()
    {
        $user = auth()->user();
        if ($user->role === UserRole::FREELANCER) {
            $user->role = UserRole::CLIENT;
            $user->save();
            return $this->ok('User role changed to Client');
        }
        $user->role = UserRole::FREELANCER;
        $user->save();
        return $this->ok('User role changed to Freelance');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
