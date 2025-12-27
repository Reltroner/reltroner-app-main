<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SSOController;
use App\Http\Middleware\EnsureSSOAuthenticated;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Login Breeze dialihkan ke SSO
Route::get('/login', fn () => redirect()->route('sso.login'))->name('login');

// Root → Dashboard
Route::get('/', fn () => redirect()->route('dashboard'));

/*
|--------------------------------------------------------------------------
| SSO Routes (Keycloak)
|--------------------------------------------------------------------------
*/

Route::get('/sso/login', [SSOController::class, 'redirect'])
    ->name('sso.login');

Route::get('/sso/callback', [SSOController::class, 'callback'])
    ->name('sso.callback');

Route::get('/logout', function () {
    Session::flush();

    return redirect(
        config('services.keycloak.base_url')
        . '/realms/' . config('services.keycloak.realm')
        . '/protocol/openid-connect/logout'
        . '?redirect_uri=' . urlencode(url('/'))
    );
})->name('logout');

/*
|--------------------------------------------------------------------------
| Gateway Protected Routes (SSO ONLY)
|--------------------------------------------------------------------------
*/

Route::middleware([EnsureSSOAuthenticated::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Breeze Auth Routes
|--------------------------------------------------------------------------
| Tetap disertakan agar Blade & helper tidak error
| Tapi TIDAK DIPAKAI
*/

require __DIR__ . '/auth.php';
