<?php

use App\Http\Controllers\Api\v1\MessageController;
use App\Http\Controllers\Api\v1\PusherController;
use Illuminate\Http\Request;
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
    return view('pusher');
});

Route::post('/send-message', [MessageController::class, 'sendMessage']);
Route::get('/conversation/{conversationId}/messages', [MessageController::class, 'getMessages']);
Route::get('/pusher-credentials', function () {
    return response()->json([
        'PUSHER_APP_KEY' => env('PUSHER_APP_KEY'),
        'PUSHER_APP_CLUSTER' => env('PUSHER_APP_CLUSTER'),
    ]);
});


Broadcast::routes(['middleware' => ['auth:api']]);
Route::post('/broadcasting/auth', [PusherController::class, 'authenticate'])->middleware('auth:api');



require __DIR__.'/auth.php';
