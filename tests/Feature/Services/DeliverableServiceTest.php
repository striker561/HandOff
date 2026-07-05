<?php

use App\Data\Deliverables\SaveDeliverableData;
use App\Enums\Deliverable\DeliverableAction;
use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Deliverable\DeliverableType;
use App\Enums\User\AccountRole;
use App\Events\Deliverable\DeliverableEvent;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Services\DeliverableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(DeliverableService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);
});

it('creates a deliverable', function () {
    $milestone = Milestone::factory()->create(['project_unique_id' => $this->project->unique_id]);

    $deliverable = $this->service->createDeliverable(SaveDeliverableData::fromArray([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $this->admin->unique_id,
        'name' => 'Test Deliverable',
        'type' => DeliverableType::FILE->value,
    ]), $this->admin);

    expect($deliverable)->toBeInstanceOf(Deliverable::class)
        ->and($deliverable->name)->toBe('Test Deliverable')
        ->and($deliverable->status)->toBe(DeliverableStatus::DRAFT);
});

it('updates a deliverable', function () {
    Event::fake([DeliverableEvent::class]);

    $milestone = Milestone::factory()->create(['project_unique_id' => $this->project->unique_id]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'name' => 'Original',
    ]);

    $updated = $this->service->updateDeliverable($deliverable, SaveDeliverableData::fromArray([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'name' => 'Renamed',
        'type' => DeliverableType::FILE->value,
    ]), $this->admin);

    expect($updated->name)->toBe('Renamed');

    Event::assertDispatched(DeliverableEvent::class, function (DeliverableEvent $event) use ($deliverable) {
        return $event->action === DeliverableAction::UPDATED
            && $event->deliverable->is($deliverable);
    });
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
