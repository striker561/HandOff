<?php

use App\Data\Projects\CreateProjectData;
use App\Enums\Milestone\MilestoneStatus;
use App\Enums\Project\ProjectStatus;
use App\Enums\User\AccountRole;
use App\Models\Credential;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Services\CredentialService;
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
    expect(fn () => $this->service->createProject(
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

it('builds project overview data with milestone and deliverable stats', function () {
    $project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);
    Milestone::factory()->count(2)->create([
        'project_unique_id' => $project->unique_id,
        'status' => MilestoneStatus::IN_PROGRESS,
        'order' => 1,
    ]);
    Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'status' => MilestoneStatus::COMPLETED,
        'order' => 2,
    ]);

    $overview = $this->service->getProjectOverview($project);

    expect($overview->milestonesTotal)->toBe(3)
        ->and($overview->milestonesCompleted)->toBe(1)
        ->and($overview->milestonesInProgress)->toBe(2)
        ->and($overview->progressPercentage)->toBe(33.33)
        ->and($overview->milestones)->toHaveCount(3);
});

it('caches project overview stats until they are forgotten', function () {
    $project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);

    Credential::factory()->count(2)->create([
        'project_unique_id' => $project->unique_id,
    ]);

    $cached = $this->service->getProjectOverview($project);

    Credential::factory()->create([
        'project_unique_id' => $project->unique_id,
    ]);

    expect($this->service->getProjectOverview($project)->credentialsTotal)
        ->toBe($cached->credentialsTotal);

    $this->service->forgetProjectOverview($project);

    expect($this->service->getProjectOverview($project)->credentialsTotal)
        ->toBe($cached->credentialsTotal + 1);
});

it('forgets overview cache when a credential is created via domain event', function () {
    $project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);

    expect($this->service->getProjectOverview($project)->credentialsTotal)->toBe(0);

    app(CredentialService::class)->createCredential([
        'project_unique_id' => $project->unique_id,
        'name' => 'Production Login',
        'type' => 'login',
        'password' => 'secret-password',
    ], $this->admin);

    expect($this->service->getProjectOverview($project)->credentialsTotal)->toBe(1);
});

it('changes project status', function () {
    $project = Project::factory()->create([
        'client_unique_id' => $this->client->unique_id,
        'status' => ProjectStatus::PLANNING,
    ]);

    $updated = $this->service->changeStatus($project, ProjectStatus::ACTIVE, $this->admin);

    expect($updated->status)->toBe(ProjectStatus::ACTIVE);
});
