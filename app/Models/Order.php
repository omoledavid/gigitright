<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'client_id',
        'talent_id',
        'gig_id',
        'plan_name',
        'start_date',
        'end_date',
        'amount',
        'status',
        'talent_mark_as_complete',
        'client_mark_as_complete',
        'delivered_at',
        'due_date',
    ];
    
    protected $casts = [
        'client_mark_as_complete' => 'boolean',
        'talent_mark_as_complete' => 'boolean',
        'delivered_at' => 'datetime',
        'due_date' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
    public function talent()
    {
        return $this->belongsTo(User::class, 'talent_id');
    }
    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
