<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{
    AuthenticatedSessionController,
    NewPasswordController,
    PasswordController,
    PasswordResetLinkController,
    ProfileController
};

Route::prefix('/auth')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('guest')
        ->name('login');

    // ADDED RATE LIMIT TO PREVENT SPAM, THOUGH LARAVEL STILL THROTTLES IT
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware(['guest', 'throttle:5,1'])
        ->name('password.email');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('guest')
        ->name('password.store');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    Route::put('/password', [PasswordController::class, 'update'])
        ->middleware('auth:sanctum')
        ->name('password.update');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->middleware('auth:sanctum')
        ->name('profile.update');
});

