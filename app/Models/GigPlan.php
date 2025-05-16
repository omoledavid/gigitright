<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GigPlan extends Model
{
    protected $fillable = [
        'gig_id',
        'name',
        'features',
        'price'
    ];
    protected $casts = [
        'features' => 'array'
    ];
    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }
}
