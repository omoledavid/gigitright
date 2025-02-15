<?php

namespace App\Http\Controllers\Api\v1;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Filters\v1\UserFilter;
use App\Http\Resources\v1\UserResource;
use App\Models\Category;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    use ApiResponses;
    public function categories()
    {
        $categories = Category::all();
        return Category::all();
    }
    public function talents(UserFilter $filter)
    {
        return $this->ok('success', UserResource::collection(User::query()->where('role', UserRole::FREELANCER)->where('status', UserStatus::ACTIVE)->filter($filter)->get()));
    }
}
