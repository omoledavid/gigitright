<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\MessageResource;
use App\Models\MediaFile;
use App\Models\Message;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use ApiResponses;
    public function sendMessage(Request $request)
    {
        $validatedData = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string|max:255',
            'files' => 'nullable',
        ],
        [
            'conversation_id.exists' => 'This conversation does not exist',
        ]);
        $validatedData['sender_id'] = $request->user()->id;
        $validatedData['read'] = false;
        $message = Message::query()->create($validatedData);
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
                }catch (\Exception $exception){
                    return $this->error($exception->getMessage());
                }
            }

        }
//        if ($request->hasFile('files')) {
//            foreach ($request->file('files') as $file) {
//                $path = $file->store('media_files');
//
//                MediaFile::create([
//                    'message_id' => $message->id,
//                    'file_path' => $path,
//                    'file_type' => $file->getMimeType(),
//                    'original_name' => $file->getClientOriginalName(),
//                ]);
//            }
//        }
        return $this->ok('message sent successfully.', new MessageResource($message));
    }
}
