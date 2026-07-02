<?php

use App\Models\User;
use App\Enums\User\AccountRole;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => AccountRole::ADMIN,
    ]);
});

it('authenticates users with valid credentials', function () {
    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
});

it('prevents login with invalid password', function () {
    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('prevents login for non-existent user', function () {
    $response = $this->post('/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('logs out authenticated users', function () {
    $this->actingAs($this->user);

    $response = $this->post('/logout');

    $response->assertStatus(302);
    $this->assertGuest();
});

it('redirects authenticated users to dashboard', function () {
    $response = $this->actingAs($this->user)->get('/login');

    $response->assertStatus(302);
    $response->assertRedirect('/dashboard');
});

it('redirects register to login', function () {
    $response = $this->get('/register');

    $response->assertStatus(302);
    $response->assertRedirect('/login');
    $response->assertSessionHas('info', 'Registration is closed. New accounts are created by invitation only.');
});
