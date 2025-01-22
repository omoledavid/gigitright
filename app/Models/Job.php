<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Job extends Model
{
    protected $guarded = ['id'];
    protected $casts = [
        'budget' => 'integer',
        'category_id' => 'integer',
        'sub_category_id' => 'integer',
        'deadline' => 'date',
        'skill_requirements' => 'array',
        'attachments' => 'array'
    ];
    public function applicants(): HasMany
    {
        return $this->hasMany(JobApplicants::class);
    }
}
