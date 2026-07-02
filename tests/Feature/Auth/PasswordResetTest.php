<?php

use App\Models\User;
use App\Enums\User\AccountRole;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => AccountRole::ADMIN,
    ]);
});

it('sends a password reset link', function () {
    Notification::fake();

    $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    Notification::assertSentTo($this->user, ResetPassword::class);
});

it('does not send a reset link for unknown email', function () {
    Notification::fake();

    $response = $this->post('/forgot-password', [
        'email' => 'unknown@example.com',
    ]);

    $response->assertSessionHasErrors('email');
    Notification::assertNothingSent();
});

it('allows password reset with valid token', function () {
    Notification::fake();

    $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    Notification::assertSentTo($this->user, ResetPassword::class, function (object $notification) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status', __('passwords.reset'));

        $this->assertTrue(
            Hash::check('new-password', $this->user->fresh()->password),
            'The password should have been updated to the new value.'
        );

        return true;
    });
});

it('rejects password reset with mismatched confirmation', function () {
    Notification::fake();

    $this->post('/forgot-password', [
        'email' => 'test@example.com',
    ]);

    Notification::assertSentTo($this->user, ResetPassword::class, function (object $notification) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'wrong-confirmation',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        return true;
    });
});

it('rejects password reset with invalid token', function () {
    $response = $this->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('email');
});
