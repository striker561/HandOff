<?php

use App\Enums\User\AccountRole;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ClientService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
});

it('creates a client', function () {
    $client = $this->service->createClient([
        'name' => 'Test Client',
        'email' => 'client@test.com',
    ], $this->admin);

    expect($client)->toBeInstanceOf(User::class)
        ->and($client->role)->toBe(AccountRole::CLIENT)
        ->and($client->name)->toBe('Test Client');
});

it('lists clients with pagination', function () {
    User::factory()->count(5)->create(['role' => AccountRole::CLIENT]);

    $result = $this->service->getClients();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(5);
});

it('filters clients by search', function () {
    User::factory()->create(['name' => 'John Doe', 'role' => AccountRole::CLIENT]);
    User::factory()->create(['name' => 'Jane Smith', 'role' => AccountRole::CLIENT]);

    $result = $this->service->getClients(['search' => 'John']);

    expect($result->total())->toBe(1);
});

it('finds a client by unique id', function () {
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    expect($this->service->findClient($client->unique_id)?->is($client))->toBeTrue()
        ->and($this->service->findClient('missing-id'))->toBeNull();
});

it('searches clients for select', function () {
    User::factory()->create(['name' => 'Alice Client', 'role' => AccountRole::CLIENT]);
    User::factory()->create(['name' => 'Bob Client', 'role' => AccountRole::CLIENT]);

    $results = $this->service->searchClientsForSelect('Alice');

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Alice Client');
});

it('returns no clients for blank select search', function () {
    User::factory()->create(['role' => AccountRole::CLIENT]);

    expect($this->service->searchClientsForSelect(''))->toBeEmpty();
});

it('does not find admins when looking up a client', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    expect($this->service->findClient($admin->unique_id))->toBeNull();
});

it('resends invitation for unverified clients', function () {
    $client = User::factory()->unverified()->create(['role' => AccountRole::CLIENT]);

    $this->service->resendInvitation($client, $this->admin);

    expect(true)->toBeTrue();
});

it('rejects resend for verified clients', function () {
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    expect(fn () => $this->service->resendInvitation($client, $this->admin))
        ->toThrow(ValidationException::class);
});
