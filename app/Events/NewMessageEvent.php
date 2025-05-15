<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Use a private channel based on the conversation ID
        return new Channel('conversation.' . $this->message->conversation_id);
    }

    // Optional: Customize the event name
    public function broadcastAs()
    {
        return 'new.message';
    }

    // Optional: Customize the data that gets broadcast
    public function broadcastWith()
    {
        // Load any relationships you need
        $this->message->load(['sender', 'mediaFiles']);
        
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
                // Add other sender fields you need
            ],
            'message' => $this->message->message,
            'files' => $this->message->mediaFiles,
            'created_at' => $this->message->created_at,
            'read' => $this->message->read,
        ];
    }
}