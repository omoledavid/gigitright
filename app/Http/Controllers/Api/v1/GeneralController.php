<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function categories()
    {
        $categories = Category::all();
        return Category::all();
    }
}
