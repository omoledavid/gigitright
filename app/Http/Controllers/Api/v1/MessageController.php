<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\MessageSent;
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
            'message' => 'required|string|max:255',
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
                    $path = fileUploader($file, 'messaging');
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

        // **Broadcast the message to the conversation participants**
        broadcast(new MessageSent($message))->toOthers();

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
    
}
