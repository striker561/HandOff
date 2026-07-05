<?php

use App\Enums\User\AccountRole;
use App\Models\Meeting;
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
    $this->meeting = Meeting::factory()->create(['project_unique_id' => $this->project->unique_id]);
});

it('allows admins and project clients to view meetings', function () {
    expect(Gate::forUser($this->admin)->allows('view', $this->meeting))->toBeTrue()
        ->and(Gate::forUser($this->client)->allows('view', $this->meeting))->toBeTrue();
});

it('denies clients from viewing meetings outside their project', function () {
    expect(Gate::forUser($this->otherClient)->allows('view', $this->meeting))->toBeFalse();
});

it('allows admins and project clients to create meetings', function () {
    expect(Gate::forUser($this->admin)->allows('create', [Meeting::class, $this->project]))->toBeTrue()
        ->and(Gate::forUser($this->client)->allows('create', [Meeting::class, $this->project]))->toBeTrue();
});

it('denies clients from creating meetings outside their project', function () {
    expect(Gate::forUser($this->otherClient)->allows('create', [Meeting::class, $this->project]))->toBeFalse();
});

it('allows admins and project clients to update meetings', function () {
    expect(Gate::forUser($this->admin)->allows('update', $this->meeting))->toBeTrue()
        ->and(Gate::forUser($this->client)->allows('update', $this->meeting))->toBeTrue();
});

it('denies clients from updating meetings outside their project', function () {
    expect(Gate::forUser($this->otherClient)->allows('update', $this->meeting))->toBeFalse();
});

it('denies deleting meetings for everyone', function () {
    expect(Gate::forUser($this->admin)->allows('delete', $this->meeting))->toBeFalse()
        ->and(Gate::forUser($this->client)->allows('delete', $this->meeting))->toBeFalse();
});

it('allows admins and project clients to reschedule cancel and add notes', function (string $ability) {
    expect(Gate::forUser($this->admin)->allows($ability, $this->meeting))->toBeTrue()
        ->and(Gate::forUser($this->client)->allows($ability, $this->meeting))->toBeTrue();
})->with(['reschedule', 'cancel', 'addNotes']);

it('denies clients from meeting actions outside their project', function (string $ability) {
    expect(Gate::forUser($this->otherClient)->allows($ability, $this->meeting))->toBeFalse();
})->with(['reschedule', 'cancel', 'addNotes']);
