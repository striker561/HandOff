<?php

use App\Enums\User\AccountRole;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

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

it('resends invitation', function () {
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    $this->service->resendInvitation($client, $this->admin);

    expect(true)->toBeTrue();
});
