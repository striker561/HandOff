<?php

use App\Enums\Milestone\MilestoneStatus;
use App\Livewire\Agency\Projects\Milestones\MilestonesList;
use App\Livewire\Agency\Projects\Milestones\SaveMilestone;
use App\Models\Deliverable;
use App\Models\Milestone;
use Livewire\Livewire;

it('loads the project milestones page for admins', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();

    $this->actingAs($admin)
        ->get(route('agency.projects.milestones', ['projectUniqueId' => $project->unique_id]))
        ->assertSuccessful()
        ->assertSeeLivewire(MilestonesList::class);
});

it('shows guided empty state on milestones tab when none exist', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();

    $this->actingAs($admin)
        ->get(route('agency.projects.milestones', ['projectUniqueId' => $project->unique_id]))
        ->assertSuccessful()
        ->assertSee(__('No milestones yet'))
        ->assertSee(__('Phases of the handoff'))
        ->assertSee(__('Add milestone'));
});

it('creates a milestone from the modal', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();

    Livewire::actingAs($admin)
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('name', 'Discovery Phase')
        ->set('description', 'Initial research')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('milestone-created');

    expect(Milestone::query()->where('name', 'Discovery Phase')->exists())->toBeTrue();
});

it('lists milestones on the project hub', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
    Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Design Phase',
        'order' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(MilestonesList::class, ['projectUniqueId' => $project->unique_id])
        ->assertSee('Design Phase');
});

it('links milestones to filtered deliverables page', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $milestone = Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'order' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(MilestonesList::class, ['projectUniqueId' => $project->unique_id])
        ->assertSee(route('agency.projects.deliverables', [
            'projectUniqueId' => $project->unique_id,
            'milestone' => $milestone->unique_id,
        ], false));
});

it('updates a milestone from the save modal', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $milestone = Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Old Name',
        'order' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $milestone->unique_id)
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('milestone-created');

    expect($milestone->fresh()->name)->toBe('Updated Name');
});

it('shows a read-only status when editing a completed milestone', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $milestone = Milestone::factory()->completed()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Done Phase',
        'order' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $milestone->unique_id)
        ->assertSee(__('Completed'))
        ->assertSee(__('Completed automatically when all deliverables in this milestone are approved.'))
        ->assertDontSee('wire:model="status"', false);
});

it('creates a milestone with the selected status', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();

    Livewire::actingAs($admin)
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('name', 'Build Phase')
        ->set('status', MilestoneStatus::IN_PROGRESS->value)
        ->call('save')
        ->assertHasNoErrors();

    expect(Milestone::query()->where('name', 'Build Phase')->first()->status)
        ->toBe(MilestoneStatus::IN_PROGRESS);
});

it('allows admins to delete empty milestones from the list', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $milestone = Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'order' => 1,
        'status' => MilestoneStatus::PENDING,
    ]);

    Livewire::actingAs($admin)
        ->test(MilestonesList::class, ['projectUniqueId' => $project->unique_id])
        ->call('deleteMilestone', uniqueId: $milestone->unique_id)
        ->assertHasNoErrors();

    expect(Milestone::query()->where('unique_id', $milestone->unique_id)->exists())->toBeFalse();
});

it('locks due date when editing a milestone with deliverables', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $milestone = Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'due_date' => now()->addDays(7),
        'order' => 1,
    ]);
    Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
    ]);

    Livewire::actingAs($admin)
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $milestone->unique_id)
        ->assertSet('dueDateLocked', true)
        ->assertSee(__('Due date is fixed once deliverables are linked to this milestone.'))
        ->set('due_date', now()->addDays(30)->format('Y-m-d'))
        ->set('name', 'Renamed Phase')
        ->call('save')
        ->assertHasNoErrors();

    $fresh = $milestone->fresh();
    expect($fresh->name)->toBe('Renamed Phase')
        ->and($fresh->due_date?->toDateString())->toBe($milestone->due_date?->toDateString());
});
