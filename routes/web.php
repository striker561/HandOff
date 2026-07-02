<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Registration is disabled — redirect to login gracefully
Route::get('/register', function () {
    return redirect()->route('login')->with('info', 'Registration is closed. New accounts are created by invitation only.');
})->name('register');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('profile', 'settings/profile');
});

require __DIR__ . '/settings.php';
