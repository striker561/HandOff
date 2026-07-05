<?php

use App\Livewire\Agency\Projects\Credentials\CredentialsList;
use App\Livewire\Agency\Projects\Credentials\SaveCredential;
use App\Livewire\Agency\Projects\Credentials\ViewCredential;
use App\Models\Credential;
use Livewire\Livewire;

it('loads the credentials tab for admins', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();

    $this->actingAs($admin)
        ->get(route('agency.projects.credentials', ['projectUniqueId' => $project->unique_id]))
        ->assertSuccessful()
        ->assertSeeLivewire(CredentialsList::class);
});

it('does not expose encrypted credential fields in the list', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();

    Credential::factory()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Production Login',
        'username' => 'deploy-user',
        'url' => 'https://secret.example.com/admin',
    ]);

    Livewire::actingAs($admin)
        ->test(CredentialsList::class, ['projectUniqueId' => $project->unique_id])
        ->assertSee('Production Login')
        ->assertDontSee('deploy-user')
        ->assertDontSee('secret.example.com');
});

it('creates an encrypted credential', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();

    Livewire::actingAs($admin)
        ->test(SaveCredential::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('name', 'Staging Login')
        ->set('type', 'login')
        ->set('username', 'admin')
        ->set('password', 'secret-password')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('credential-created');

    $credential = Credential::query()->where('name', 'Staging Login')->first();

    expect($credential)->not->toBeNull();
    expect($credential->password)->toBe('secret-password');
    expect($credential->getRawOriginal('password'))->not->toBe('secret-password');
});

it('reveals encrypted credential details on demand', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $credential = Credential::factory()->create([
        'project_unique_id' => $project->unique_id,
        'username' => 'vault-user',
        'password' => 'vault-secret',
        'url' => 'https://vault.example.com',
        'notes' => 'Rotate quarterly',
    ]);

    Livewire::actingAs($admin)
        ->test(ViewCredential::class)
        ->call('open', uniqueId: $credential->unique_id, projectUniqueId: $project->unique_id)
        ->assertSet('detailsRevealed', false)
        ->call('revealDetails')
        ->assertSet('detailsRevealed', true)
        ->assertSet('username', 'vault-user')
        ->assertSet('revealedPassword', 'vault-secret')
        ->assertSet('url', 'https://vault.example.com')
        ->assertSet('notes', 'Rotate quarterly');
});

it('opens save modal when editing from view modal', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $credential = Credential::factory()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Production Login',
    ]);

    Livewire::actingAs($admin)
        ->test(ViewCredential::class)
        ->call('open', uniqueId: $credential->unique_id, projectUniqueId: $project->unique_id)
        ->call('edit')
        ->assertDispatched('open-save-credential', projectUniqueId: $project->unique_id, uniqueId: $credential->unique_id);

    Livewire::actingAs($admin)
        ->test(SaveCredential::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $credential->unique_id)
        ->assertSet('name', 'Production Login');
});

it('updates a credential from the save modal without changing password', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $credential = Credential::factory()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Old Credential',
        'password' => 'unchanged-secret',
    ]);

    Livewire::actingAs($admin)
        ->test(SaveCredential::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $credential->unique_id)
        ->set('name', 'Updated Credential')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('credential-updated');

    expect($credential->fresh()->name)->toBe('Updated Credential');
    expect($credential->fresh()->password)->toBe('unchanged-secret');
});
