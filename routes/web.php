<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Hanya satu route dengan name 'dashboard'
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/login/keycloak', function () {
    return Socialite::driver('keycloak')->redirect();
});

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
