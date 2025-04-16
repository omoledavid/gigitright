<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'is_marked_complete_by_talent' => 'boolean'
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
    public function talent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function client()
    {
        return $this->hasOneThrough(
            User::class,     // Final related model
            Job::class,      // Intermediate model
            'id',            // Foreign key on Job table...
            'id',            // Foreign key on User table...
            'job_id',        // Local key on Milestone table (this model)
            'user_id'        // Local key on Job table (points to client user)
        );
    }
}
