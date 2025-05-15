<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    Log::info('Broadcast auth hit:', ['user' => $user, 'conversation' => $conversationId]);
    return Conversation::where('id', $conversationId)
        ->where(function ($query) use ($user) {
            $query->where('user_id', $user->id) // Check if the user is a participant
            ->orWhere('client_id', $user->id); // Adjust based on your DB structure
        })
        ->exists();
});


