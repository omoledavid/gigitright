<?php

namespace App\Models;

use App\Enums\ReviewType;
use App\Http\Filters\v1\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gig extends Model
{
    protected $guarded = ['id'];
    protected $with = ['gigPlan'];
    protected $casts = [
        'skills' => 'array',
        'previous_works_companies' => 'array',
    ];

    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id')->where('type', ReviewType::GIG);
    }
    public function gigPlan()
    {
        return $this->hasMany(GigPlan::class);
    }
}
