<?php

namespace App\Http\Filters\v1;

class CommunityFilter extends QueryFilter
{
    public function include($value)
    {
        return $this->builder->with($value);
    }
    public function status($value)
    {
        return $this->builder->where('is_private', $value);
    }
    public function id($value)
    {
        return $this->builder->whereIn('id', explode(',', $value));
    }
    public function name($value)
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }
    public function createdAt($value)
    {
        $dates = explode(',', $value);
        if(count($dates) > 1){
            return $this->builder->whereBetween('created_at', $dates);
        }
        return $this->builder->whereDate('created_at', $value);
    }
    public function updatedAt($value)
    {
        $dates = explode(',', $value);
        if(count($dates) > 1){
            return $this->builder->whereBetween('updated_at', $dates);
        }
        return $this->builder->whereDate('updated_at', $value);
    }
}
