<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    DashboardController,
    ProfileController,
    SSOController
};

use App\Http\Controllers\Modules\FinanceRedirectController;
use App\Http\Middleware\EnsureSSOAuthenticated;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| - Tidak memerlukan SSO
| - Entry awal sebelum autentikasi
|--------------------------------------------------------------------------
*/

// Breeze login → selalu diarahkan ke SSO
Route::get('/login', fn () => redirect()->route('sso.login'))
    ->name('login');

// Root → dashboard jika sudah SSO, jika belum → SSO login
Route::get('/', function () {
    if (session('sso_authenticated')) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('sso.login');
});


/*
|--------------------------------------------------------------------------
| SSO Routes (Keycloak) — PHASE 2 (FROZEN)
|--------------------------------------------------------------------------
| - JANGAN diubah flow-nya
| - Ini adalah auth authority
|--------------------------------------------------------------------------
*/

Route::get('/sso/login', [SSOController::class, 'redirect'])
    ->name('sso.login');

Route::get('/sso/callback', [SSOController::class, 'callback'])
    ->name('sso.callback');

Route::get('/logout', function () {
    $idToken = session('id_token');

    session()->flush();

    $query = http_build_query([
        'post_logout_redirect_uri' => config('app.url') . '/',
        'id_token_hint'            => $idToken,
    ]);

    return redirect()->away(
        rtrim(config('services.keycloak.base_url'), '/')
        . '/realms/' . config('services.keycloak.realm')
        . '/protocol/openid-connect/logout?'
        . $query
    );
})->name('logout');


/*
|--------------------------------------------------------------------------
| Gateway Protected Routes (SSO ONLY)
|--------------------------------------------------------------------------
| - User sudah tervalidasi oleh Keycloak
| - Gateway memegang session SSO
|--------------------------------------------------------------------------
*/

Route::middleware(['web', EnsureSSOAuthenticated::class])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Gateway Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Profile (Gateway-local)
        |--------------------------------------------------------------------------
        */
        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::patch('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::delete('/profile', [ProfileController::class, 'destroy'])
            ->name('profile.destroy');


        /*
        |--------------------------------------------------------------------------
        | ERP Module Routers — PHASE 3
        |--------------------------------------------------------------------------
        | - Gateway = token issuer
        | - Gateway = redirector
        | - Module = PASIF (consume only)
        |--------------------------------------------------------------------------
        */

        Route::get('/modules/finance', FinanceRedirectController::class)
            ->name('modules.finance');

        // future (Phase 4+)
        // Route::get('/modules/hrm', HrmRedirectController::class);
        // Route::get('/modules/inventory', InventoryRedirectController::class);
    });


/*
|--------------------------------------------------------------------------
| Breeze Auth Routes (INERT)
|--------------------------------------------------------------------------
| - Dibiarkan agar Blade & helper tidak error
| - TIDAK dipakai sebagai auth
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
