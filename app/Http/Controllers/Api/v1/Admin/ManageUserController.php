<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Filters\v1\UserFilter;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ManageUserController extends Controller
{
    use ApiResponses;
    public function index(Request $request, UserFilter $filter)
    {
        // Get total user statistics
        $totalUsers = User::count();
        $totalActiveUsers = User::where('status', 'active')->count();
        $totalSuspendedUsers = User::where('status', 'suspended')->count();

        // Filter users
        $users = User::query()->where('id', '!=', auth()->id())->filter($filter)->get();

        return response()->json([
            'total_users' => $totalUsers,
            'total_active_users' => $totalActiveUsers,
            'total_suspended_users' => $totalSuspendedUsers,
            'users' => UserResource::collection($users),
        ]);
    }
    public function suspend(User $user)
    {
        if($user->status === UserStatus::BLOCKED){
            $user->update(['status' => UserStatus::ACTIVE]);
            return $this->ok('User unsuspended');
        }
        $user->update(['status' => UserStatus::BLOCKED]);
        return $this->ok('User suspended');
    }
    public function sendEmail(Request $request, User $user)
    {
        $request->validate([
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        return $this->ok('Mail sent successfully', new UserResource($user));
    }
}
