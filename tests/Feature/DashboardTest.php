<?php

use App\Enums\User\AccountRole;
use App\Models\User;

it('loads the dashboard for admin users with the app layout', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $response = $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee(__('Hey :name', ['name' => strtok($admin->name, ' ')]))
        ->assertSee(__('Dashboard'))
        ->assertSee(__('Clients'));

    preg_match_all('/<a[^>]*data-flux-sidebar-item[^>]*>/', $response->getContent(), $items);

    $activeItems = array_filter(
        $items[0],
        fn (string $item): bool => (bool) preg_match('/\sdata-current="/', $item),
    );

    expect($activeItems)->toHaveCount(1)
        ->and($activeItems[array_key_first($activeItems)])->toContain(route('dashboard', absolute: false));
});

it('loads the dashboard for client users with the portal layout', function () {
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    $this->actingAs($client)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee(__('Dashboard'))
        ->assertSee(__('My projects'));
});

it('loads the profile settings page inside the app layout', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertSuccessful()
        ->assertSee(__('Settings'))
        ->assertSee(__('Profile'))
        ->assertSee(__('Log out'));
});

it('redirects to password confirmation before security settings', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('security.edit'))
        ->assertRedirect(route('password.confirm'));
});

it('loads security settings after password confirmation', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertSuccessful()
        ->assertSee(__('Update password'));
});

it('loads the passkeys settings page when passkeys are enabled and password is confirmed', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->withSession(['auth.password_confirmed_at' => time()])
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
