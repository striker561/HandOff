<?php

use App\Enums\User\AccountRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->otherClient = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);
});

it('allows admins all project abilities via before hook', function (string $ability) {
    expect(Gate::forUser($this->admin)->allows($ability, $this->project))->toBeTrue();
})->with(['view', 'update', 'delete', 'changeStatus']);

it('allows admins to create projects via before hook', function () {
    expect(Gate::forUser($this->admin)->allows('create', Project::class))->toBeTrue();
});

it('allows clients to list and view their own projects', function () {
    expect(Gate::forUser($this->client)->allows('viewAny', Project::class))->toBeTrue()
        ->and(Gate::forUser($this->client)->allows('view', $this->project))->toBeTrue();
});

it('denies clients from viewing projects they do not own', function () {
    expect(Gate::forUser($this->otherClient)->allows('view', $this->project))->toBeFalse();
});

it('denies clients from mutating projects', function () {
    expect(Gate::forUser($this->client)->allows('create', Project::class))->toBeFalse()
        ->and(Gate::forUser($this->client)->allows('update', $this->project))->toBeFalse()
        ->and(Gate::forUser($this->client)->allows('delete', $this->project))->toBeFalse()
        ->and(Gate::forUser($this->client)->allows('changeStatus', $this->project))->toBeFalse();
});
