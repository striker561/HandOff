<?php

use App\Data\Projects\CreateProjectData;
use App\Enums\Milestone\MilestoneStatus;
use App\Enums\Project\ProjectStatus;
use App\Enums\User\AccountRole;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ProjectService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->client = User::factory()->create(['role' => AccountRole::CLIENT]);
});

it('creates a project from dto data', function () {
    $project = $this->service->createProject(
        CreateProjectData::fromArray([
            'client_unique_id' => $this->client->unique_id,
            'name' => 'Test Project',
            'description' => 'A test project',
            'currency' => 'usd',
        ]),
        $this->admin,
    );

    expect($project)->toBeInstanceOf(Project::class)
        ->and($project->name)->toBe('Test Project')
        ->and($project->status)->toBe(ProjectStatus::PLANNING);
});

it('rejects project creation for an unknown client', function () {
    expect(fn() => $this->service->createProject(
        CreateProjectData::fromArray([
            'client_unique_id' => 'missing-client-id',
            'name' => 'Test Project',
            'currency' => 'usd',
        ]),
        $this->admin,
    ))->toThrow(ValidationException::class);
});

it('finds a project by unique id', function () {
    $project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);

    expect($this->service->findProject($project->unique_id)?->is($project))->toBeTrue()
        ->and($this->service->findProject('missing-id'))->toBeNull();
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
