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
Route::post('/send-message', [MessageController::class, 'sendMessage'])->name('send.message');
Route::get('/conversation/{conversationId}/messages', [MessageController::class, 'getMessages'])->name('conversation.messages');
Route::get('/pusher-credentials', function () {
    return response()->json([
        'PUSHER_APP_KEY' => env('PUSHER_APP_KEY'),
        'PUSHER_APP_CLUSTER' => env('PUSHER_APP_CLUSTER'),
    ]);
});
//Route::post('/pusher/auth', function (Request $request) {
//
//    // Ensure the user is authenticated before allowing access to the channel
//    if (!auth()->check()) {
//        return response()->json(['message' => 'Unauthorized'], 403);
//    }
//
//    // Fetch the channel name from the request, typically something like 'chat.{conversationId}'
//    $channelName = 'chat';
//
//    // Ensure the channel name is valid and matches your intended pattern
//    if (empty($channelName)) {
//        return response()->json(['message' => 'Channel name is required'], 400);
//    }
//
//    // You can add additional validation for the channel name if necessary, for example:
//    if (!preg_match('/^chat\.\d+$/', $channelName)) {
//        return response()->json(['message' => 'Invalid channel name'], 400);
//    }
//
//    // Return the authorization response for the private channel
//    return Broadcast::auth($request);
//});
Route::post('/pusher/auth', [PusherController::class, 'authenticate'])->name('pusher.auth');
require __DIR__.'/auth.php';
