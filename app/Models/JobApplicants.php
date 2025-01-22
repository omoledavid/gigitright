<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobApplicants extends Model
{
    protected $guarded = ['id'];
    public function job(): HasOne
    {
        return $this->hasOne(Job::class, 'id', 'job_id');
    }
}
