<?php

use App\Data\Deliverables\SaveDeliverableData;
use App\Enums\Deliverable\DeliverableAction;
use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Deliverable\DeliverableType;
use App\Enums\Milestone\MilestoneAction;
use App\Enums\Milestone\MilestoneStatus;
use App\Enums\User\AccountRole;
use App\Events\Deliverable\DeliverableEvent;
use App\Events\Milestone\MilestoneEvent;
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
        'status' => DeliverableStatus::DRAFT,
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

it('approves a deliverable as the client', function () {
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    $approved = $this->service->approveDeliverable($deliverable, $this->client);

    expect($approved->status)->toBe(DeliverableStatus::APPROVED)
        ->and($approved->approved_at)->not->toBeNull();
});

it('rejects a deliverable as the client', function () {
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    $rejected = $this->service->rejectDeliverable($deliverable, $this->client, 'Needs revision');

    expect($rejected->status)->toBe(DeliverableStatus::REJECTED);
});

it('submits a deliverable for client review', function () {
    Event::fake([DeliverableEvent::class]);

    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => DeliverableStatus::DRAFT,
    ]);

    $submitted = $this->service->submitForReview($deliverable, $this->admin);

    expect($submitted->status)->toBe(DeliverableStatus::IN_REVIEW);

    Event::assertDispatched(DeliverableEvent::class, function (DeliverableEvent $event) use ($deliverable) {
        return $event->action === DeliverableAction::STATUS_CHANGED
            && $event->deliverable->is($deliverable)
            && ($event->metadata['to_status'] ?? null) === DeliverableStatus::IN_REVIEW->value;
    });
});

it('gets deliverables for a project', function () {
    Deliverable::factory()->count(3)->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    $result = $this->service->getDeliverablesForProject($this->project->unique_id);

    expect($result->total())->toBe(3);
});

it('auto-completes a milestone when all deliverables are approved', function () {
    Event::fake([MilestoneEvent::class]);

    $milestone = Milestone::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MilestoneStatus::IN_PROGRESS,
    ]);

    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'status' => DeliverableStatus::APPROVED,
    ]);

    $pending = Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    expect($milestone->fresh()->status)->toBe(MilestoneStatus::IN_PROGRESS);

    $this->service->approveDeliverable($pending, $this->client);

    expect($milestone->fresh()->status)->toBe(MilestoneStatus::COMPLETED)
        ->and($milestone->fresh()->completed_at)->not->toBeNull();

    Event::assertDispatched(MilestoneEvent::class, function (MilestoneEvent $event) use ($milestone) {
        return $event->action === MilestoneAction::COMPLETED
            && $event->milestone->is($milestone)
            && ($event->metadata['auto_completed'] ?? false) === true;
    });
});

it('reopens a completed milestone when a new deliverable is added', function () {
    Event::fake([MilestoneEvent::class, DeliverableEvent::class]);

    $milestone = Milestone::factory()->completed()->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'status' => DeliverableStatus::APPROVED,
    ]);

    $this->service->createDeliverable(SaveDeliverableData::fromArray([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $this->admin->unique_id,
        'name' => 'New Deliverable',
        'type' => DeliverableType::FILE->value,
    ]), $this->admin);

    expect($milestone->fresh()->status)->toBe(MilestoneStatus::IN_PROGRESS)
        ->and($milestone->fresh()->completed_at)->toBeNull();

    Event::assertDispatched(MilestoneEvent::class, function (MilestoneEvent $event) use ($milestone) {
        return $event->action === MilestoneAction::STATUS_CHANGED
            && $event->milestone->is($milestone)
            && ($event->metadata['auto_uncompleted'] ?? false) === true;
    });
});

it('does not auto-complete a milestone with no deliverables', function () {
    Event::fake([MilestoneEvent::class]);

    $milestone = Milestone::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MilestoneStatus::IN_PROGRESS,
    ]);

    $method = new ReflectionMethod(DeliverableService::class, 'syncMilestoneStatus');
    $method->setAccessible(true);
    $method->invoke($this->service, $milestone, $this->admin);

    expect($milestone->fresh()->status)->toBe(MilestoneStatus::IN_PROGRESS);
    Event::assertNotDispatched(MilestoneEvent::class);
});

it('syncs milestone status when a deliverable is reassigned', function () {
    Event::fake([MilestoneEvent::class, DeliverableEvent::class]);

    $sourceMilestone = Milestone::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MilestoneStatus::IN_PROGRESS,
    ]);
    $targetMilestone = Milestone::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MilestoneStatus::IN_PROGRESS,
    ]);

    Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $targetMilestone->unique_id,
        'status' => DeliverableStatus::APPROVED,
    ]);

    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $sourceMilestone->unique_id,
        'status' => DeliverableStatus::DRAFT,
    ]);

    $this->service->updateDeliverable($deliverable, SaveDeliverableData::fromArray([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $targetMilestone->unique_id,
        'name' => $deliverable->name,
        'type' => DeliverableType::FILE->value,
    ]), $this->admin);

    expect($targetMilestone->fresh()->status)->toBe(MilestoneStatus::IN_PROGRESS);
});
