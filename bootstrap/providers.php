<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\BroadcastServiceProvider::class,
    App\Providers\ConsoleServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
];
