<?php

use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Deliverable\DeliverableType;
use App\Enums\User\AccountRole;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use App\Services\DeliverableService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(DeliverableService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);
});

it('creates a deliverable', function () {
    $deliverable = $this->service->createDeliverable([
        'project_unique_id' => $this->project->unique_id,
        'created_by_unique_id' => $this->admin->unique_id,
        'name' => 'Test Deliverable',
        'type' => DeliverableType::FILE,
    ], $this->admin);

    expect($deliverable)->toBeInstanceOf(Deliverable::class)
        ->and($deliverable->name)->toBe('Test Deliverable')
        ->and($deliverable->status)->toBe(DeliverableStatus::DRAFT);
});

it('approves a deliverable', function () {
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    $approved = $this->service->approveDeliverable($deliverable, $this->admin);

    expect($approved->status)->toBe(DeliverableStatus::APPROVED)
        ->and($approved->approved_at)->not->toBeNull();
});

it('rejects a deliverable', function () {
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    $rejected = $this->service->rejectDeliverable($deliverable, $this->admin, 'Needs revision');

    expect($rejected->status)->toBe(DeliverableStatus::REJECTED);
});

it('gets deliverables for a project', function () {
    Deliverable::factory()->count(3)->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    $result = $this->service->getDeliverablesForProject($this->project->unique_id);

    expect($result->total())->toBe(3);
});
