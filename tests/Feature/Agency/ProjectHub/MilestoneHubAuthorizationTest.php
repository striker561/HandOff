<?php

use App\Livewire\Agency\Projects\Milestones\SaveMilestone;
use App\Models\Milestone;
use Livewire\Livewire;

beforeEach(function () {
    bindProjectHubAuthorizationContext();
});

it('allows admins to create milestones via save modal', function () {
    Livewire::actingAs($this->admin)
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $this->project->unique_id)
        ->set('name', 'Policy Milestone')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('milestone-created');
});

it('forbids clients from creating milestones via save modal', function () {
    Livewire::actingAs($this->client)
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $this->project->unique_id)
        ->set('name', 'Client Milestone')
        ->call('save')
        ->assertForbidden();
});

it('forbids clients from updating milestones via save modal', function () {
    $milestone = Milestone::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'order' => 2,
    ]);

    Livewire::actingAs($this->client)
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $this->project->unique_id, uniqueId: $milestone->unique_id)
        ->set('name', 'Client Update Attempt')
        ->call('save')
        ->assertForbidden();
});
