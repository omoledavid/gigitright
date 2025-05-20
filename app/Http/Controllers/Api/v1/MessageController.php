<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\NewMessageEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\MessageResource;
use App\Models\MediaFile;
use App\Models\Message;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    use ApiResponses;
    public function sendMessage(Request $request)
    {
        $validatedData = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'nullable|string|max:255',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,gif,svg,pdf,doc,docx|max:2048',
        ], [
            'conversation_id.exists' => 'This conversation does not exist',
        ]);

        $validatedData['sender_id'] = auth()->id();
        $validatedData['read'] = false;

        $message = Message::create($validatedData);

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
            try {
                $location = getFilePath('messaging');
                $path = fileUploader($file, $location);
                MediaFile::create([
                'message_id' => $message->id,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
                ]);
            } catch (\Exception $exception) {
                return $this->error($exception->getMessage());
            }
            }
        }

        // Broadcast the event
        broadcast(new NewMessageEvent($message))->toOthers();

        return $this->ok('Message sent successfully.', new MessageResource($message));
    }
    public function getMessages($conversationId)
    {
        // Fetch messages for the given conversation
        $messages = Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc') // Optional: Order messages by creation date
            ->get(['message', 'created_at']); // Get only the 'message' and 'created_at' columns

        return response()->json($messages);
    }
    public function readMessage($id)
    {
        $message = Message::query()->where('id', $id)->first()
        ;
            if (!$message) {
                return $this->error('Message not found.', 404);
            }

            $message->read = true;
            $message->save();

            return $this->ok('Message marked as read.', new MessageResource($message));
    }
    
}
