<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\CommunityRoleStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\CommunityMemberResource;
use App\Http\Resources\v1\CommunityResource;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $communities = Community::paginate(10);

        return $this->ok('success', ['communities' => CommunityResource::collection($communities)]);
    }

    /**
     * Show the form for creating a new resource.
     * (Not required for API-based controllers)
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:communities|max:255',
            'slug' => 'required|unique:communities|max:255',
            'description' => 'nullable|string',
            'is_private' => 'boolean',
        ]);
        if (preg_match("/[^a-zA-Z0-9_]/", $request->slug)) {
            return $this->error('No special characters or capital letters are allowed in the name field.', 400);
        }
        $validatedData['created_by'] = auth()->id();

        $community = Community::create($validatedData);

        return $this->ok('community created', ['community' => $community], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Community $community)
    {
        return $this->ok('success', ['community' => new CommunityResource($community)]);
    }

    /**
     * Show the form for editing the specified resource.
     * (Not required for API-based controllers)
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Community $community)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|max:255|unique:communities,name,' . $community->id,
            'slug' => 'sometimes|required|max:255|unique:communities,slug,' . $community->id,
            'description' => 'nullable|string',
            'is_private' => 'boolean',
        ]);

        $community->update($validated);

        return $this->ok('community updated', ['community' => new CommunityResource($community)]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Community $community)
    {
        $community->delete();

        return response()->json([
            'message' => 'Community deleted successfully',
        ]);
    }
    public function member(Community $community)
    {
        return $this->ok('success', ['all_members' => CommunityMemberResource::collection($community->members)]);
    }
    public function joinCommunity(Request $request)
    {
        $validatedData = $request->validate([
            'community_id' => 'required|exists:communities,id',
        ]);
        $alreadyJoined = CommunityMember::query()->where('community_id', $validatedData['community_id'])->where('user_id', auth()->id())->exists();
        if ($alreadyJoined) {
            return $this->error("You've already joined this community!");
        }
        $validatedData['user_id'] = auth()->id();
        $validatedData['role'] = CommunityRoleStatus::MEMBER;
        $communityMember = CommunityMember::create($validatedData);
        return $this->ok('community member created', ['communityMember' => new CommunityMemberResource($communityMember)]);
    }
}
