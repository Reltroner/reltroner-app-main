<?php
// routes/web.php
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Str;

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
    try {
        $keycloakUser = Socialite::driver('keycloak')->stateless()->user();

        if (!$keycloakUser->getEmail()) {
            throw new \Exception('Email not returned from Keycloak.');
        }

        // ⬇⬇⬇ SISIPKAN DI SINI
        $user = User::firstOrCreate(
            ['email' => $keycloakUser->getEmail()],
            [
                'name' => $keycloakUser->getName() ?? 'Unknown',
                'password' => bcrypt(Str::random(16)), // fallback password
            ]
        );

        auth()->login($user);
        return redirect('/dashboard');
    } catch (\Exception $e) {
        \Log::error('SSO Callback Error: ' . $e->getMessage(), [
            'trace' => $e->getTrace(),
        ]);
        abort(500, 'SSO login failed. Check application logs.');
    }
});


require __DIR__.'/auth.php';
