<?php

use App\Enums\Deliverable\DeliverableStatus;
use App\Livewire\Agency\Projects\Deliverables\DeliverablesList;
use App\Livewire\Agency\Projects\Deliverables\SaveDeliverable;
use App\Models\Deliverable;
use Livewire\Livewire;

beforeEach(function () {
    bindProjectHubAuthorizationContext();
});

it('allows admins to create deliverables via save modal', function () {
    Livewire::actingAs($this->admin)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $this->project->unique_id, milestoneUniqueId: $this->milestone->unique_id)
        ->set('name', 'Policy Test Deliverable')
        ->set('milestone_unique_id', $this->milestone->unique_id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('deliverable-created');

    expect(Deliverable::query()->where('name', 'Policy Test Deliverable')->exists())->toBeTrue();
});

it('forbids clients from creating deliverables via save modal', function () {
    Livewire::actingAs($this->client)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $this->project->unique_id, milestoneUniqueId: $this->milestone->unique_id)
        ->set('name', 'Client Deliverable')
        ->set('milestone_unique_id', $this->milestone->unique_id)
        ->call('save')
        ->assertForbidden();
});

it('allows admins to open but forbids saving in-review deliverables', function () {
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $this->milestone->unique_id,
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    Livewire::actingAs($this->admin)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $this->project->unique_id, uniqueId: $deliverable->unique_id)
        ->assertSet('name', $deliverable->name)
        ->assertSet('readOnly', true)
        ->set('name', 'Blocked Update')
        ->call('save')
        ->assertHasNoErrors();

    expect($deliverable->fresh()->name)->toBe($deliverable->name);
});

it('forbids resubmitting in-review deliverables from the list', function () {
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'milestone_unique_id' => $this->milestone->unique_id,
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    Livewire::actingAs($this->admin)
        ->test(DeliverablesList::class, ['projectUniqueId' => $this->project->unique_id])
        ->call('submitForReview', uniqueId: $deliverable->unique_id)
        ->assertForbidden();
});
