<?php

use App\Enums\Milestone\MilestoneStatus;
use App\Enums\User\AccountRole;
use App\Livewire\Agency\Projects\ViewProject;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('loads the project overview page for admins', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create([
        'client_unique_id' => $client->unique_id,
        'name' => 'Hub Project',
    ]);
    Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Discovery',
        'status' => MilestoneStatus::IN_PROGRESS,
        'order' => 1,
    ]);

    $this->actingAs($admin)
        ->get(route('agency.projects.show', ['projectUniqueId' => $project->unique_id]))
        ->assertSuccessful()
        ->assertSee('Hub Project')
        ->assertSee(__('Overview'))
        ->assertSee(__('Project progress'))
        ->assertSee(__('Milestone pipeline'))
        ->assertSee('Discovery');
});

it('forbids client users from project hub pages', function () {
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    $this->actingAs($client)
        ->get(route('agency.projects.show', ['projectUniqueId' => $project->unique_id]))
        ->assertForbidden();
});

it('returns not found for unknown project unique ids', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('agency.projects.show', ['projectUniqueId' => Str::uuid()->toString()]))
        ->assertNotFound();
});

it('shows open project link in the view flyout', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create([
        'client_unique_id' => $client->unique_id,
        'name' => 'Flyout Project',
    ]);

    Livewire::actingAs($admin)
        ->test(ViewProject::class)
        ->call('open', uniqueId: $project->unique_id)
        ->assertSee(__('Open project'))
        ->assertSee(route('agency.projects.show', ['projectUniqueId' => $project->unique_id]));
});

it('shows milestones-first empty state on deliverables tab when no milestones exist', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();

    $this->actingAs($admin)
        ->get(route('agency.projects.deliverables', ['projectUniqueId' => $project->unique_id]))
        ->assertSuccessful()
        ->assertSee(__('Add milestones first'))
        ->assertSee(__('Go to milestones'));
});
