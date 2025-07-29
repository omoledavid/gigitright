<?php

use App\Console\Commands\DeleteExpCoupon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command(DeleteExpCoupon::class)->dailyAt('00:00');
