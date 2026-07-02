<?php

use App\Enums\Milestone\MilestoneStatus;
use App\Enums\Project\ProjectStatus;
use App\Enums\User\AccountRole;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ProjectService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->client = User::factory()->create(['role' => AccountRole::CLIENT]);
});

it('creates a project', function () {
    $project = $this->service->create([
        'client_unique_id' => $this->client->unique_id,
        'name' => 'Test Project',
        'description' => 'A test project',
        'status' => ProjectStatus::PLANNING,
    ]);

    expect($project)->toBeInstanceOf(Project::class)
        ->and($project->name)->toBe('Test Project');
});

it('calculates progress as 0 when no milestones', function () {
    $project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);

    $progress = $this->service->calculateProgress($project);

    expect($progress)->toBe(0.0);
});

it('calculates progress based on completed milestones', function () {
    $project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);
    Milestone::factory()->count(3)->create([
        'project_unique_id' => $project->unique_id,
        'status' => MilestoneStatus::IN_PROGRESS,
    ]);
    Milestone::factory()->count(1)->create([
        'project_unique_id' => $project->unique_id,
        'status' => MilestoneStatus::COMPLETED,
    ]);

    $progress = $this->service->calculateProgress($project);

    expect($progress)->toBe(25.0);
});

it('changes project status', function () {
    $project = Project::factory()->create([
        'client_unique_id' => $this->client->unique_id,
        'status' => ProjectStatus::PLANNING,
    ]);

    $updated = $this->service->changeStatus($project, ProjectStatus::ACTIVE, $this->admin);

    expect($updated->status)->toBe(ProjectStatus::ACTIVE);
});
