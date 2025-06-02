<?php
// app/Providers/RouteServiceProvider.php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();
        // Global rate limit
        RateLimiter::for('global', fn ($request) =>
            Limit::perMinute(60)->by($request->ip())
        );

        // Register routes manually (no $this->routes())
        Route::middleware('web', 'throttle:global')
            ->group(base_path('routes/web.php'));

        Route::middleware('web', 'throttle:global')
            ->group(base_path('routes/console.php'));

        Route::middleware('web', 'throttle:global')
            ->group(base_path('routes/auth.php'));

        Route::middleware('api')
        ->prefix('api')
        ->group(base_path('routes/api.php'));
    }
}
