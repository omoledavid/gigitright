<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register the broadcasting routes
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        // Register the channel routes
        require base_path('routes/channels.php');
    }
}
