<?php

use App\Enums\Milestone\MilestoneStatus;
use App\Enums\User\AccountRole;
use App\Models\Deliverable;
use App\Models\Milestone;
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
    $this->milestone = Milestone::factory()->create(['project_unique_id' => $this->project->unique_id]);
});

it('allows admins and project clients to view milestones', function () {
    expect(Gate::forUser($this->admin)->allows('view', $this->milestone))->toBeTrue()
        ->and(Gate::forUser($this->client)->allows('view', $this->milestone))->toBeTrue();
});

it('denies clients from viewing milestones outside their project', function () {
    expect(Gate::forUser($this->otherClient)->allows('view', $this->milestone))->toBeFalse();
});

it('allows admins to create milestones on a project', function () {
    expect(Gate::forUser($this->admin)->allows('create', [Milestone::class, $this->project]))->toBeTrue();
});

it('denies clients from creating milestones', function () {
    expect(Gate::forUser($this->client)->allows('create', [Milestone::class, $this->project]))->toBeFalse();
});

it('allows admins to update milestones', function () {
    expect(Gate::forUser($this->admin)->allows('update', $this->milestone))->toBeTrue();
});

it('denies clients from updating milestones', function () {
    expect(Gate::forUser($this->client)->allows('update', $this->milestone))->toBeFalse();
});

it('allows admins to delete empty milestones that are not completed', function () {
    expect(Gate::forUser($this->admin)->allows('delete', $this->milestone))->toBeTrue();
});

it('denies deleting milestones with deliverables', function () {
    Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $this->milestone->unique_id,
    ]);

    expect(Gate::forUser($this->admin)->allows('delete', $this->milestone))->toBeFalse();
});

it('denies deleting completed milestones', function () {
    $this->milestone->update(['status' => MilestoneStatus::COMPLETED]);

    expect(Gate::forUser($this->admin)->allows('delete', $this->milestone))->toBeFalse();
});

it('denies clients from deleting milestones', function () {
    expect(Gate::forUser($this->client)->allows('delete', $this->milestone))->toBeFalse();
});

it('allows admins to change milestone status and reorder', function () {
    expect(Gate::forUser($this->admin)->allows('updateStatus', $this->milestone))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('reorder', [Milestone::class, $this->project]))->toBeTrue();
});

it('denies clients from changing milestone status or reordering', function () {
    expect(Gate::forUser($this->client)->allows('updateStatus', $this->milestone))->toBeFalse()
        ->and(Gate::forUser($this->client)->allows('reorder', [Milestone::class, $this->project]))->toBeFalse();
});
