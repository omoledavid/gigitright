<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function include(String $relationship): bool
    {
        $param = request()->get('include');
        if(!isset($param)){
            return false;
        }
        $includesValues = explode(',', strtolower($param));
        return in_array(strtolower($relationship), $includesValues);
    }
}
