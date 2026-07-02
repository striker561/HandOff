<?php

use App\Enums\User\AccountRole;
use App\Models\User;

it('loads the dashboard for admin users with the workspace shell', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee(__('Hey :name', ['name' => strtok($admin->name, ' ')]))
        ->assertSee(__('Dashboard'))
        ->assertSee(__('Clients'));
});

it('loads the dashboard for client users with the portal shell', function () {
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    $this->actingAs($client)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee(__('Dashboard'))
        ->assertSee(__('My projects'));
});

it('loads the profile settings page inside the workspace shell', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertSuccessful()
        ->assertSee(__('Settings'))
        ->assertSee(__('Profile'))
        ->assertSee(__('Log out'));
});

it('loads the passkeys settings page when passkeys are enabled', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('passkeys.edit'))
        ->assertSuccessful()
        ->assertSee(__('Passkeys'));
});

it('redirects the appearance settings route to profile', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('appearance.edit'))
        ->assertRedirect(route('profile.edit'));
});
