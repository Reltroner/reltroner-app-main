<?php
// routes/web.php
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// Redirect root ke dashboard
Route::get('/', fn () => redirect('/dashboard'));

// Dashboard (1 name only)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profile (hanya untuk user yang sudah login)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Keycloak SSO login & callback
Route::get('/login/keycloak', fn () => Socialite::driver('keycloak')->redirect())->name('login.keycloak');

Route::get('/login/keycloak/callback', function () {
    $keycloakUser = Socialite::driver('keycloak')->stateless()->user();

    $user = \App\Models\User::firstOrCreate([
        'email' => $keycloakUser->email,
    ], [
        'name' => $keycloakUser->name ?? $keycloakUser->nickname,
    ]);

    auth()->login($user);

    return redirect('/dashboard');
});

require __DIR__.'/auth.php';
