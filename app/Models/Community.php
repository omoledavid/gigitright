<?php

namespace App\Models;

use App\Http\Filters\v1\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    protected $guarded = ['id'];

    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class, 'community_id');
    }
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'community_id');
    }
}
