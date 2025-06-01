<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
