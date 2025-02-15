<?php

namespace App\Http\Filters\v1;

class UserFilter extends QueryFilter
{
    public function include($value)
    {
        return $this->builder->with($value);
    }
    public function country($value)
    {
        return $this->builder->where('country', $value);
    }
    public function id($value)
    {
        return $this->builder->whereIn('id', explode(',', $value));
    }
    public function user_title($value)
    {
        return $this->builder->whereHas('profile', function ($query) use ($value) {
            $query->where('user_title', 'like', '%' . $value . '%');
        });
    }
    public function skill($value)
    {
        return $this->builder->whereHas('profile', function ($query) use ($value) {
            $query->where('skills', 'like', '%' . $value . '%');
        });
    }
    public function languages($value)
    {
        return $this->builder->whereHas('profile', function ($query) use ($value) {
            $query->where('languages', 'like', '%' . $value . '%');
        });
    }

    public function description($value)
    {
        return $this->builder->where('description', 'like', '%' . $value . '%');
    }
    public function duration($value)
    {
        return $this->builder->where('duration', 'like', '%' . $value . '%');
    }
    public function createdAt($value)
    {
        $dates = explode(',', $value);
        if(count($dates) > 1){
            return $this->builder->whereBetween('created_at', $dates);
        }
        return $this->builder->whereDate('created_at', $value);
    }
    public function budget($value)
    {
        $budget = explode(',', $value);
        if(count($budget) > 1){
            return $this->builder->whereBetween('budget', $budget);
        }
        return $this->builder->where('budget', $value);
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
