<?php

use App\Livewire\Settings\Passkeys;
use App\Livewire\Settings\Security;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'settings.profile')->name('profile.edit');
    Route::redirect('settings/appearance', 'settings/profile')->name('appearance.edit');
    Route::livewire('settings/security', Security::class)->name('security.edit');
    Route::livewire('settings/passkeys', Passkeys::class)->name('passkeys.edit');
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('passkeys.edit'),
        'manage' => route('passkeys.edit'),
    ]);
})->name('well-known.passkeys');
