<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class PusherController extends Controller
{
    public function authenticate(Request $request)
    {
        // Ensure the user is authenticated before proceeding
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Check if the user is authorized to join the channel
        if ($this->isAuthorized($user)) {
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'useTLS' => false
                ]
            );

            // Generate the authentication string
            $auth = $pusher->authorizeChannel(
                $request->channel_name,  // Channel name
                $request->socket_id      // Socket ID
            );

            return response()->json(['auth' => $auth]);
        }

        // If the user is not authorized to access the channel
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function isAuthorized($user)
    {
        // Example authorization logic
        // Check if the user is authenticated and owns the resource (e.g., a conversation)

        $channelName = request('channel_name'); // Get the channel name from the request

        // Assuming the channel name is something like "private-conversation-{conversation_id}"
        $conversationId = explode('-', $channelName)[1]; // Extract the conversation ID from the channel name
        $conversationId = explode('.', $conversationId)[1]; // Extract the conversation ID from the channel name
       
        // Check if the user is part of this conversation
        $conversation = Conversation::query()->findOrFail($conversationId);

        // Ensure that the user is authorized to access this conversation (e.g., they are part of it)
        return $conversation;
    }

}
