<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'sender_id',
        'sender_type',
        'message',
        'attachment',
    ];

    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function sender()
    {
        return $this->morphTo();
    }
}
