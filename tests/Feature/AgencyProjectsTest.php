<?php

use App\Enums\User\AccountRole;
use App\Livewire\Agency\Projects\ProjectsList;
use App\Livewire\Agency\Projects\SaveProject;
use App\Livewire\Agency\Projects\ViewProject;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

it('loads the projects page for admins', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('agency.projects.index'))
        ->assertSuccessful()
        ->assertSee(__('Projects'))
        ->assertSeeLivewire(ProjectsList::class)
        ->assertSeeLivewire(SaveProject::class)
        ->assertSeeLivewire(ViewProject::class);
});

it('forbids client users from the agency projects page', function () {
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    $this->actingAs($client)
        ->get(route('agency.projects.index'))
        ->assertForbidden();
});

it('dispatches open-project-view with the project unique id', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    Livewire::actingAs($admin)
        ->test(ProjectsList::class)
        ->call('viewProject', $project->unique_id)
        ->assertDispatched('open-project-view', uniqueId: $project->unique_id);
});

it('loads project details when opened by unique id', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['name' => 'Acme Corp', 'role' => AccountRole::CLIENT]);
    $project = Project::factory()->create([
        'client_unique_id' => $client->unique_id,
        'name' => 'Website Redesign',
        'description' => 'A full redesign',
        'budget' => 1000,
        'currency' => 'usd',
    ]);

    Livewire::actingAs($admin)
        ->test(ViewProject::class)
        ->call('open', uniqueId: $project->unique_id)
        ->assertSet('uniqueId', $project->unique_id)
        ->assertSet('name', 'Website Redesign')
        ->assertSet('clientName', 'Acme Corp')
        ->assertSet('formattedBudget', '$1,000.00')
        ->assertSee(__('Open project'));
});

it('creates a project from the modal', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    Livewire::actingAs($admin)
        ->test(SaveProject::class)
        ->set('client_unique_id', $client->unique_id)
        ->set('name', 'Website Redesign')
        ->set('currency', 'usd')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('project-created');

    expect(Project::query()->where('name', 'Website Redesign')->exists())->toBeTrue();
});

it('updates a project from the modal', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create([
        'client_unique_id' => $client->unique_id,
        'name' => 'Old Name',
    ]);

    Livewire::actingAs($admin)
        ->test(SaveProject::class)
        ->call('open', uniqueId: $project->unique_id)
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('project-updated');

    expect($project->fresh()->name)->toBe('Updated Name');
});

it('searches clients when typing in the save modal', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['name' => 'Searchable Client', 'role' => AccountRole::CLIENT]);
    User::factory()->create(['name' => 'Other Person', 'role' => AccountRole::CLIENT]);

    Livewire::actingAs($admin)
        ->test(SaveProject::class)
        ->set('clientSearch', 'Searchable')
        ->assertSee('Searchable Client')
        ->assertDontSee('Other Person')
        ->call('selectClient', $client->unique_id)
        ->assertSet('client_unique_id', $client->unique_id)
        ->assertSet('clientSearch', '');
});

it('creates a project with a custom hex color', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    Livewire::actingAs($admin)
        ->test(SaveProject::class)
        ->set('client_unique_id', $client->unique_id)
        ->set('name', 'Branded Project')
        ->set('currency', 'usd')
        ->set('color', '#ff00aa')
        ->call('save')
        ->assertHasNoErrors();

    expect(Project::query()->where('name', 'Branded Project')->value('color'))->toBe('#ff00aa');
});

it('filters projects when search is updated', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    Project::factory()->create([
        'client_unique_id' => $client->unique_id,
        'name' => 'Alpha Project',
    ]);
    Project::factory()->create([
        'client_unique_id' => $client->unique_id,
        'name' => 'Beta Project',
    ]);

    Livewire::actingAs($admin)
        ->test(ProjectsList::class)
        ->set('search', 'Alpha')
        ->assertSee('Alpha Project')
        ->assertDontSee('Beta Project');
});
