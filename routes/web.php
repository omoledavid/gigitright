<?php

use App\Http\Controllers\Api\v1\MessageController;
use App\Http\Controllers\Api\v1\PusherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return [
//        'Project Title' => 'Gigitright',
//        'Agency' => 'Albanny Technogy',
//        'Developer' => 'Omole Kessiena',
//        'Laravel' => app()->version()
//    ];
//});
Route::get('/', function () {
    return redirect(env('FRONTEND_URL'));
});
Route::get('/login', function () {
    
    // Simulate user authentication using Auth::login
    $user = \App\Models\User::where('email', 'omolekessiena@gmail.com')->first();

    // Log in the user
    Auth::login($user);
    $token = $user->createToken('auth_token')->plainTextToken;
    // Store the token in the session with the key 'myapitoken'
    session(['myapitoken' => $token]);

    return response()->json([
        'message' => 'Login successful',
        'user' => [
            'email' => $user->email,
        ],
        'token' => $token,
    ]);
})->name('login');
Route::get('/check-auth', function() {
    // Check if the user is authenticated
    if (Auth::check()) {
        return response()->json([
            'message' => 'User is authenticated',
            'user' => Auth::user(),
        ]);
    } else {
        return response()->json([
            'message' => 'User is not authenticated',
        ], 401);
    }
})->middleware('auth:sanctum');

Route::get('/pusher-credentials', function () {
    return response()->json([
        'PUSHER_APP_KEY' => env('PUSHER_APP_KEY'),
        'PUSHER_APP_CLUSTER' => env('PUSHER_APP_CLUSTER'),
    ]);
});

Route::get('/debug-session', function () {
    dd([
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'config' => config('session'),
    ]);
});

