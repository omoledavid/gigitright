<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\CommunityRoleStatus;
use App\Http\Controllers\Controller;
use App\Http\Filters\v1\CommunityFilter;
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
        $communities = Community::query()->where('created_by', auth()->id())->paginate(10);

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
        
        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => auth()->id(),
            'role' => CommunityRoleStatus::ADMIN,
        ]);
        $community->members_count = $community->members_count + 1;
        $community->save();

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
        //check if the user is the owner of the community
        if ($community->created_by !== auth()->id()) {
            return $this->error('You are not the owner of this community');
        }
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
        $community = Community::query()->findOrFail($communityMember->community_id);
        $community->members_count = $community->members_count + 1;
        $community->save();
        return $this->ok('You\'ve successfully joined this community!', ['communityMember' => new CommunityMemberResource($communityMember)]);
    }
    public function leaveCommunity($id)
    {
        $community = Community::query()->find($id);
        if (!$community) {
            return $this->error("Community not found");
        }
        $alreadyJoined = CommunityMember::query()->where('community_id', $id)->where('user_id', auth()->id())->exists();
        if (!$alreadyJoined) {
            return $this->error("You're not a part of this community!");
        }
        $communityMember = CommunityMember::query()->where('community_id', $id)->where('user_id', auth()->id())->delete();
        $community->members_count = -1;
        $community->save();
        return $this->ok('You left this community!');
    }
    public function viewAllCommunities(CommunityFilter $filter)
    {
        return $this->ok('success', ['communities' => CommunityResource::collection(Community::filter($filter)->paginate())]);
    }

    public function suggestedCommunities()
    {
        $user = auth()->user();

        // 1. Get communities the user has already joined
        $joinedCommunityIds = $user->communities()->pluck('communities.id')->toArray();

        // 2. Suggest popular communities (most members)
        $popularCommunities = Community::whereNotIn('id', $joinedCommunityIds)
            ->orderByDesc('members_count')
            ->limit(5)
            ->get();

        // 3. Suggest new & trending communities (latest created)
        $newCommunities = Community::whereNotIn('id', $joinedCommunityIds)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // 4. Suggest communities joined by friends
//        $friends = $user->friends ?? []; // Assuming `friends()` is a relationship in User model
//        $friendCommunityIds = Community::whereHas('users', function ($query) use ($friends) {
//            $query->whereIn('users.id', $friends);
//        })->pluck('id');
//
//        $friendCommunities = Community::whereIn('id', $friendCommunityIds)
//            ->whereNotIn('id', $joinedCommunityIds)
//            ->limit(5)
//            ->get();
        return $this->ok('success', [
            'popular_communities' => CommunityResource::collection($popularCommunities),
            'new_communities' => CommunityResource::collection($newCommunities),
        ]);
    }
    public function joinedCommunities()
    {
        $user = auth()->user();
        $joinedCommunity = $user->communities()->get();
        return $this->ok('success', CommunityResource::collection($joinedCommunity));
    }
}
