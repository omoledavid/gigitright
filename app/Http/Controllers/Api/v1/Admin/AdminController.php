<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    private function widgetData() {
        $widget['total_users']             = User::count();
        $widget['active_users']          = User::active()->count();
        $widget['total_jobs']          = Job::count();
        return $widget;
    }
}
