<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Filters\v1\UserFilter;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ManageUserController extends Controller
{
    use ApiResponses;
    public function index(UserFilter $filter)
    {
        return $this->ok('All users', UserResource::collection(User::query()->filter($filter)->get()));
    }
}
