<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class JobInvite extends Model
{
    protected $fillable = [
        'application_id',
        'client_id',
        'talent_id',
        'status',
        'message',
    ];
    public function applicants()
    {
        return $this->belongsTo(JobApplicants::class, 'applicantion_id', 'id');
    }
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id', 'id');
    }
    public function job(): HasOneThrough
    {
        return $this->hasOneThrough(Job::class, JobApplicants::class, 'id', 'id', 'application_id', 'job_id');
    }
    public function talent(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, JobApplicants::class, 'id', 'id', 'application_id', 'user_id');
    }
}
