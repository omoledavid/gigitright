<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Enums\Status;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginUserRequest;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponses;
    public function register(Request $request)
    {
        if (!gs('register_status')) {
            return $this->error('Registration is currently disabled. Please try again later.', 403);
        }
        $validatedData = request()->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'country' => 'required',
            'role' => 'required|in:freelancer,client',
        ]);
        if (preg_match("/[^a-zA-Z0-9_ ]/", $request->name)) {
            $response[] = 'No special characters or capital letters are allowed in the name field.';
            return $this->error($response, 400);
        }
        $validatedData['username'] = generateUniqueUsername($validatedData['name']);

        $validatedData['status'] = Status::ACTIVE;
        $validatedData['sv'] = Status::ACTIVE;
        //create user
        $verificationCode = verificationCode(6);
        $user = User::create($validatedData);
        if (gs('ev') == 1) {
            $user->ev = UserStatus::ACTIVE;
            $user->email_verified_at = Carbon::now();
            $user->save();
        } else {
            $user->ver_code         = $verificationCode;
            $user->ver_code_send_at = Carbon::now();
            $user->save();
            notify($user, 'EVER_CODE', [
                'code' => $user->ver_code,
            ]);
        }


        // Trigger email verification event
        //event(new Registered($user));
        $token = $user->createToken('auth_token', ['*'])->plainTextToken;
        //        $token = $user->createToken('auth_token',['*'], now()->addDay())->plainTextToken;
        return $this->ok('User registered successfully. Please verify your email address.', [
            'user' => new UserResource($user),
            'token' => $token,
            // 'verification_code' => $verificationCode,
        ]);
    }
    public function login(LoginUserRequest $request)
    {
        if (!gs('login_status')) {
            return $this->error('Login is currently disabled. Please try again later.', 403);
        }
        $request->validated($request->all());
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return $this->error('Invalid credentials', 401);
        }

        $user = User::firstWhere('email', $request->email);
        if ($user->status === UserStatus::BLOCKED) {
            return $this->error('Your account has been blocked. Please contact support.', 403);
        }
        // $token = $user->createToken('auth_token',['*'], now()->addDay())->plainTextToken;
        $token = $user->createToken('auth_token', ['*'])->plainTextToken;

        return $this->ok(
            'Authenticated',
            [
                'token' => $token,
                'user' => $user
            ]
        );
    }
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->ok('Logged out');
    }
    public function verify($id)
    {
        return $id;
    }
    public function changePassword(Request $request)
    {
        $validatedData = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user = Auth::user();
        try {
            if (Hash::check($validatedData['current_password'], $user->password)) {
                $user->password = Hash::make($validatedData['password']);
                $user->save();
                return $this->ok('Password changed');
            } else {
                return $this->error('Wrong current password', 401);
            }
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 401);
        }
    }
    public function changeEmail(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
        ]);
        $user = Auth::user();
        $user->email = $validatedData['email'];
        $user->save();
        return $this->ok('Email changed');
    }
}
