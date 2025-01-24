<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Enums\Status;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginUserRequest;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponses;
    public function register(Request $request)
    {
        $validatedData = request()->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required',
        ]);
        if (preg_match("/[^a-zA-Z0-9_ ]/", $request->name)) {
            $response[] = 'No special characters or capital letters are allowed in the name field.';
            return $this->error($response, 400);
        }
        $validatedData['username'] = generateUniqueUsername($validatedData['name']);

        $validatedData['status'] = Status::ACTIVE;
        $validatedData['sv'] = Status::ACTIVE;


        $user = User::create($validatedData);


        // Trigger email verification event
//        event(new Registered($user));
        $token = $user->createToken('auth_token',['*'])->plainTextToken;
//        $token = $user->createToken('auth_token',['*'], now()->addDay())->plainTextToken;
        return $this->ok('User registered successfully. Please verify your email address.', [
            'user' => $user,
            'token' => $token
        ]);
    }
    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());
        if(!Auth::attempt($request->only(['email', 'password']))) {
            return $this->error('Invalid credentials', 401);
        }

        $user = User::firstWhere('email', $request->email);
        $token = $user->createToken('auth_token',['*'], now()->addDay())->plainTextToken;

        return $this->ok(
            'Authenticated',
            [
                'token' => $token
            ]
        );
    }
    public function logout(Request $request):JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->ok('Logged out');
    }
    public function verify($id)
    {
        return $id;
    }
}
