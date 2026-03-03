<?php
// routes/web.php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    DashboardController,
    SSOController
};

use App\Http\Controllers\Modules\FinanceRedirectController;
use App\Http\Middleware\EnsureSSOAuthenticated;

/*
|--------------------------------------------------------------------------
| Breeze Auth Routes (INERT BUT REQUIRED FOR VIEWS)
|--------------------------------------------------------------------------
| Load FIRST so we can override logout route later.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Root → dashboard if authenticated, otherwise SSO login
Route::get('/', function () {
    return session('sso_authenticated')
        ? redirect()->route('dashboard')
        : redirect()->route('sso.login');
});


/*
|--------------------------------------------------------------------------
| SSO Routes (Keycloak Authority)
|--------------------------------------------------------------------------
*/

Route::get('/sso/login', [SSOController::class, 'redirect'])
    ->name('sso.login');

Route::get('/sso/callback', [SSOController::class, 'callback'])
    ->name('sso.callback');


/*
|--------------------------------------------------------------------------
| OIDC Compliant Logout (OVERRIDE BREEZE)
|--------------------------------------------------------------------------
| Must be declared AFTER auth.php
|--------------------------------------------------------------------------
*/

Route::post('/logout', [SSOController::class, 'logout'])
    ->name('logout');

Route::get('/logged-out', [SSOController::class, 'loggedOut'])
    ->name('logged.out');


/*
|--------------------------------------------------------------------------
| Gateway Protected Routes (SSO ONLY)
|--------------------------------------------------------------------------
*/

Route::middleware([EnsureSSOAuthenticated::class])
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/modules/finance', FinanceRedirectController::class)
            ->name('modules.finance');

    });