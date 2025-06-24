<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\SupportTicketResource;
use App\Http\Resources\v1\TicketMessageResource;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    use ApiResponses;
    // List all tickets for authenticated user
    public function index()
    {
        $tickets = SupportTicket::with('latestMessage')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return $this->ok('Tickets retrieved successfully', SupportTicketResource::collection($tickets));
    }

    // Create a new ticket
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'priority' => $request->priority,
            'status' => 'open',
        ]);

        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => auth()->id(),
            'sender_type' => User::class,
            'message' => $request->message,
        ]);

        return $this->ok('Ticket created successfully', new SupportTicketResource($ticket), 201);
    }

    // Show a ticket and its conversation
    public function show($id)
    {
        $ticket = SupportTicket::with('messages.sender')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return $this->ok('Ticket retrieved successfully', new SupportTicketResource($ticket));
    }

    // Add message to a ticket
    public function addMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'nullable|string',
            'attachment' => 'nullable|file|max:2048', // max 2MB, you can adjust
        ]);

        $ticket = SupportTicket::findOrFail($id);

        if ($ticket->status === 'closed') {
            return response()->json(['message' => 'Ticket is closed. Cannot reply.'], 403);
        }

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            // $attachmentPath = $request->file('attachment')->store('support_attachments', 'public');
            $attachmentPath = fileUploader($request->certificate_file, getFilePath('ticket_attachments'));
        }

        if (empty($request->message) && !$attachmentPath) {
            return $this->error('Message or attachment is required.', 422);
        }

        $message = TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => auth()->id(),
            'sender_type' => User::class,
            'message' => $request->message,
            'attachment' => $attachmentPath,
        ]);

        return $this->ok('Reply sent successfully', new TicketMessageResource($message), 201);
    }

    // Close a ticket
    public function close($id)
    {
        $ticket = SupportTicket::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $ticket->update(['status' => 'closed']);
        return $this->ok('Ticket closed successfully', new SupportTicketResource($ticket));
    }
}
