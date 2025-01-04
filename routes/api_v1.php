<?php

use App\Http\Controllers\Api\v1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/testing', function (Request $request) {
    return 'hello world';
});
Route::get('/register', function (Request $request) {
    return 'working';
});
Route::post('login', AuthController::class . '@login');
