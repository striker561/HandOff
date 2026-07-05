<?php

use App\Enums\User\AccountRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->unverifiedClient = User::factory()->unverified()->create(['role' => AccountRole::CLIENT]);
});

it('allows admins to list and invite clients', function () {
    expect(Gate::forUser($this->admin)->allows('viewAny', User::class))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('create', User::class))->toBeTrue();
});

it('denies clients from listing or inviting users', function () {
    expect(Gate::forUser($this->client)->allows('viewAny', User::class))->toBeFalse()
        ->and(Gate::forUser($this->client)->allows('create', User::class))->toBeFalse();
});

it('allows admins to view client records', function () {
    expect(Gate::forUser($this->admin)->allows('view', $this->client))->toBeTrue();
});

it('denies admins from viewing other admins via client view policy', function () {
    $otherAdmin = User::factory()->create(['role' => AccountRole::ADMIN]);

    expect(Gate::forUser($this->admin)->allows('view', $otherAdmin))->toBeFalse();
});

it('allows admins to resend invitations to unverified clients only', function () {
    expect(Gate::forUser($this->admin)->allows('resendInvitation', $this->unverifiedClient))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('resendInvitation', $this->client))->toBeFalse();
});

it('denies clients from resending invitations', function () {
    expect(Gate::forUser($this->client)->allows('resendInvitation', $this->unverifiedClient))->toBeFalse();
});
