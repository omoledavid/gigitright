<?php

namespace App\Http\Filters\v1;

class JobFilter extends QueryFilter
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
    public function title($value)
    {
        return $this->builder->where('title', 'like', '%' . $value . '%');
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
