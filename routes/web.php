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
| Breeze Auth Routes (INERT BUT REQUIRED FOR VIEWS)
|--------------------------------------------------------------------------
| Load FIRST so we can override logout route later.
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Breeze login → always redirect to SSO
Route::get('/login', fn () => redirect()->route('sso.login'))
    ->name('login');

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

        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::patch('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        Route::delete('/profile', [ProfileController::class, 'destroy'])
            ->name('profile.destroy');

        Route::get('/modules/finance', FinanceRedirectController::class)
            ->name('modules.finance');

        // Dev only
        Route::get('/__session-test', function () {
            session(['test' => 'ok']);
            return session('test');
        });
    });