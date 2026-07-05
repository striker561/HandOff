<?php

use App\Enums\User\AccountRole;
use App\Models\Credential;
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
    $this->credential = Credential::factory()->create(['project_unique_id' => $this->project->unique_id]);
});

it('allows admins and project clients to view credentials', function () {
    expect(Gate::forUser($this->admin)->allows('view', $this->credential))->toBeTrue()
        ->and(Gate::forUser($this->client)->allows('view', $this->credential))->toBeTrue();
});

it('denies clients from viewing credentials outside their project', function () {
    expect(Gate::forUser($this->otherClient)->allows('view', $this->credential))->toBeFalse();
});

it('allows admins to create credentials on a project', function () {
    expect(Gate::forUser($this->admin)->allows('create', [Credential::class, $this->project]))->toBeTrue();
});

it('denies clients from creating credentials', function () {
    expect(Gate::forUser($this->client)->allows('create', [Credential::class, $this->project]))->toBeFalse();
});

it('allows admins to update credentials', function () {
    expect(Gate::forUser($this->admin)->allows('update', $this->credential))->toBeTrue();
});

it('denies clients from updating credentials', function () {
    expect(Gate::forUser($this->client)->allows('update', $this->credential))->toBeFalse();
});

it('denies deleting credentials for everyone', function () {
    expect(Gate::forUser($this->admin)->allows('delete', $this->credential))->toBeFalse()
        ->and(Gate::forUser($this->client)->allows('delete', $this->credential))->toBeFalse();
});

it('allows admins to reveal credential passwords', function () {
    expect(Gate::forUser($this->admin)->allows('reveal', $this->credential))->toBeTrue();
});

it('denies clients from revealing credential passwords', function () {
    expect(Gate::forUser($this->client)->allows('reveal', $this->credential))->toBeFalse();
});
