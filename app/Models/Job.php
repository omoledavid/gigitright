<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use App\Http\Filters\v1\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
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
    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }
    public function applicants(): HasMany
    {
        return $this->hasMany(JobApplicants::class,'job_id','id');
    }
    public function relatedJobs()
    {
        return $this->hasMany(Job::class, 'category_id', 'category_id')
            ->where('id', '!=', $this->id)
            ->latest()
            ->limit(5);
    }
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->where('status', MilestoneStatus::IN_PROGRESS);
    }
}
