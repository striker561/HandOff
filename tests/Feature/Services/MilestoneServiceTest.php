<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Milestone;
use App\Enums\User\AccountRole;
use App\Enums\Milestone\MilestoneStatus;
use App\Services\MilestoneService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(MilestoneService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);
});

it('creates an ordered milestone', function () {
    $milestone = $this->service->createOrderedMilestone([
        'name' => 'First Milestone',
        'project_unique_id' => $this->project->unique_id,
        'due_date' => now()->addDays(7),
    ]);

    expect($milestone)->toBeInstanceOf(Milestone::class)
        ->and($milestone->name)->toBe('First Milestone')
        ->and($milestone->order)->toBe(1);
});

it('creates milestones with sequential ordering', function () {
    $this->service->createOrderedMilestone([
        'name' => 'First',
        'project_unique_id' => $this->project->unique_id,
    ]);
    $this->service->createOrderedMilestone([
        'name' => 'Second',
        'project_unique_id' => $this->project->unique_id,
    ]);

    $milestones = Milestone::where('project_unique_id', $this->project->unique_id)
        ->orderBy('order')
        ->get();

    expect($milestones[0]->order)->toBe(1)
        ->and($milestones[1]->order)->toBe(2);
});

it('updates milestone status', function () {
    $milestone = Milestone::factory()->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    $updated = $this->service->updateStatus(
        $milestone,
        MilestoneStatus::COMPLETED,
        $this->admin
    );

    expect($updated->status)->toBe(MilestoneStatus::COMPLETED)
        ->and($updated->completed_at)->not->toBeNull();
});

it('gets milestones for a project', function () {
    Milestone::factory()->count(3)->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    $result = $this->service->getMilestonesForProject($this->project->unique_id);

    expect($result->total())->toBe(3);
});
