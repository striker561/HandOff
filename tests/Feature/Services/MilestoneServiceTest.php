<?php

use App\Data\Milestones\SaveMilestoneData;
use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Milestone\MilestoneAction;
use App\Enums\Milestone\MilestoneStatus;
use App\Enums\User\AccountRole;
use App\Events\Milestone\MilestoneEvent;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use App\Services\MilestoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(MilestoneService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);
});

it('creates an ordered milestone', function () {
    Event::fake([MilestoneEvent::class]);

    $milestone = $this->service->createOrderedMilestone(SaveMilestoneData::fromArray([
        'name' => 'First Milestone',
        'project_unique_id' => $this->project->unique_id,
        'due_date' => now()->addDays(7)->toDateString(),
    ]), $this->admin);

    expect($milestone)->toBeInstanceOf(Milestone::class)
        ->and($milestone->name)->toBe('First Milestone')
        ->and($milestone->order)->toBe(1);

    Event::assertDispatched(MilestoneEvent::class, function (MilestoneEvent $event) use ($milestone) {
        return $event->action === MilestoneAction::CREATED
            && $event->milestone->is($milestone)
            && $event->performedBy->is($this->admin);
    });
});

it('creates milestones with sequential ordering', function () {
    $this->service->createOrderedMilestone(SaveMilestoneData::fromArray([
        'name' => 'First',
        'project_unique_id' => $this->project->unique_id,
    ]), $this->admin);
    $this->service->createOrderedMilestone(SaveMilestoneData::fromArray([
        'name' => 'Second',
        'project_unique_id' => $this->project->unique_id,
    ]), $this->admin);

    $milestones = Milestone::where('project_unique_id', $this->project->unique_id)
        ->orderBy('order')
        ->get();

    expect($milestones[0]->order)->toBe(1)
        ->and($milestones[1]->order)->toBe(2);
});

it('updates milestone fields', function () {
    Event::fake([MilestoneEvent::class]);

    $milestone = Milestone::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'name' => 'Original',
    ]);

    $updated = $this->service->updateMilestone($milestone, SaveMilestoneData::fromArray([
        'project_unique_id' => $this->project->unique_id,
        'name' => 'Renamed',
        'description' => 'Updated description',
    ]), $this->admin);

    expect($updated->name)->toBe('Renamed')
        ->and($updated->description)->toBe('Updated description');

    Event::assertDispatched(MilestoneEvent::class, function (MilestoneEvent $event) use ($milestone) {
        return $event->action === MilestoneAction::UPDATED
            && $event->milestone->is($milestone);
    });
});

it('updates milestone status', function () {
    $milestone = Milestone::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MilestoneStatus::IN_PROGRESS,
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

it('auto-completes when all deliverables are approved', function () {
    Event::fake([MilestoneEvent::class]);

    $milestone = Milestone::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MilestoneStatus::IN_PROGRESS,
    ]);

    Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'status' => DeliverableStatus::APPROVED,
    ]);

    $this->service->syncFromDeliverables($milestone, $this->admin);

    expect($milestone->fresh()->status)->toBe(MilestoneStatus::COMPLETED)
        ->and($milestone->fresh()->completed_at)->not->toBeNull();

    Event::assertDispatched(MilestoneEvent::class, function (MilestoneEvent $event) use ($milestone) {
        return $event->action === MilestoneAction::COMPLETED
            && $event->milestone->is($milestone)
            && ($event->metadata['auto_completed'] ?? false) === true;
    });
});

it('auto-reopens when a non-approved deliverable exists', function () {
    Event::fake([MilestoneEvent::class]);

    $milestone = Milestone::factory()->completed()->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'status' => DeliverableStatus::DRAFT,
    ]);

    $this->service->syncFromDeliverables($milestone, $this->admin);

    expect($milestone->fresh()->status)->toBe(MilestoneStatus::IN_PROGRESS)
        ->and($milestone->fresh()->completed_at)->toBeNull();

    Event::assertDispatched(MilestoneEvent::class, function (MilestoneEvent $event) use ($milestone) {
        return $event->action === MilestoneAction::STATUS_CHANGED
            && $event->milestone->is($milestone)
            && ($event->metadata['auto_uncompleted'] ?? false) === true;
    });
});

it('does nothing when milestone has no deliverables', function () {
    Event::fake([MilestoneEvent::class]);

    $milestone = Milestone::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MilestoneStatus::IN_PROGRESS,
    ]);

    $this->service->syncFromDeliverables($milestone, $this->admin);

    expect($milestone->fresh()->status)->toBe(MilestoneStatus::IN_PROGRESS);
    Event::assertNotDispatched(MilestoneEvent::class);
});
