<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
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
        RateLimiter::for('global', fn ($request) =>
        Limit::perMinute(60)->by($request->ip())
    );

    $this->routes(function () {
        Route::middleware('web', 'throttle:global') // tambahkan di semua web route
            ->group(base_path('routes/web.php'));
    });

    }
}
