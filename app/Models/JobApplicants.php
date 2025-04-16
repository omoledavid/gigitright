<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobApplicants extends Model
{
    protected $guarded = ['id'];
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id', 'id');
    }
    public function applicant(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class, 'user_id', 'user_id');
    }
}
