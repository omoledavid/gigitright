<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ConversationResource;
use App\Models\Conversation;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $conversations = Conversation::query()->where('client_id', $user->id)->orWhere('user_id', $user->id)->latest();
        return $this->ok('success', ['conversations' => ConversationResource::collection($conversations)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validedData = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $validedData['client_id'] = auth()->id();
        $exist = Conversation::query()
            ->where(function ($query) use ($validedData) {
            $query->where('user_id', auth()->id())
                  ->where('client_id', $validedData['user_id']);
            })
            ->orWhere(function ($query) use ($validedData) {
            $query->where('user_id', $validedData['user_id'])
                  ->where('client_id', auth()->id());
            })
            ->first();

        if ($exist) {
            return $this->ok('A conversation already exists between these users.', [
            'conversation' => new ConversationResource($exist)
            ]);
        }
        $conversation = Conversation::create($validedData);
        return $this->ok('success', ['conversation' => new ConversationResource($conversation)]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $conversation = Conversation::query()->find($id);
        return $this->ok('success', ['conversation' => new ConversationResource($conversation)]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
