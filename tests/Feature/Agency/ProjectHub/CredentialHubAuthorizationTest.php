<?php

use App\Livewire\Agency\Projects\Credentials\CredentialsList;
use App\Livewire\Agency\Projects\Credentials\SaveCredential;
use App\Livewire\Agency\Projects\Credentials\ViewCredential;
use App\Models\Credential;
use Livewire\Livewire;

beforeEach(function () {
    bindProjectHubAuthorizationContext();
});

it('allows admins to create credentials via save modal', function () {
    Livewire::actingAs($this->admin)
        ->test(SaveCredential::class)
        ->call('open', projectUniqueId: $this->project->unique_id)
        ->set('name', 'Policy Credential')
        ->set('password', 'secret-password')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('credential-created');
});

it('forbids clients from creating credentials via save modal', function () {
    Livewire::actingAs($this->client)
        ->test(SaveCredential::class)
        ->call('open', projectUniqueId: $this->project->unique_id)
        ->set('name', 'Client Credential')
        ->set('password', 'secret-password')
        ->call('save')
        ->assertForbidden();
});

it('allows admins to update credentials via save modal', function () {
    $credential = Credential::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'password' => 'unchanged',
    ]);

    Livewire::actingAs($this->admin)
        ->test(SaveCredential::class)
        ->call('open', projectUniqueId: $this->project->unique_id, uniqueId: $credential->unique_id)
        ->set('name', 'Renamed Credential')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('credential-updated');
});

it('forbids clients from updating credentials via save modal', function () {
    $credential = Credential::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'password' => 'unchanged',
    ]);

    Livewire::actingAs($this->client)
        ->test(SaveCredential::class)
        ->call('open', projectUniqueId: $this->project->unique_id, uniqueId: $credential->unique_id)
        ->set('name', 'Client Rename Attempt')
        ->call('save')
        ->assertForbidden();
});

it('allows admins to reveal passwords via view modal', function () {
    $credential = Credential::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'password' => 'vault-secret',
    ]);

    Livewire::actingAs($this->admin)
        ->test(ViewCredential::class)
        ->call('open', uniqueId: $credential->unique_id, projectUniqueId: $this->project->unique_id)
        ->call('revealDetails')
        ->assertSet('detailsRevealed', true)
        ->assertSet('revealedPassword', 'vault-secret');
});

it('forbids clients from revealing passwords via view modal', function () {
    $credential = Credential::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'password' => 'vault-secret',
    ]);

    Livewire::actingAs($this->client)
        ->test(ViewCredential::class)
        ->call('open', uniqueId: $credential->unique_id, projectUniqueId: $this->project->unique_id)
        ->call('revealDetails')
        ->assertForbidden();
});

it('allows project clients to open credentials on their project', function () {
    $credential = Credential::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'name' => 'Client Visible Credential',
    ]);

    Livewire::actingAs($this->client)
        ->test(ViewCredential::class)
        ->call('open', uniqueId: $credential->unique_id, projectUniqueId: $this->project->unique_id)
        ->assertSet('name', 'Client Visible Credential')
        ->assertSet('detailsRevealed', false);
});

it('forbids clients from opening credentials outside their project', function () {
    $credential = Credential::factory()->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    Livewire::actingAs($this->otherClient)
        ->test(SaveCredential::class)
        ->call('open', projectUniqueId: $this->project->unique_id, uniqueId: $credential->unique_id)
        ->assertForbidden();
});

it('forbids clients from viewing credentials outside their project', function () {
    $credential = Credential::factory()->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    Livewire::actingAs($this->otherClient)
        ->test(ViewCredential::class)
        ->call('open', uniqueId: $credential->unique_id, projectUniqueId: $this->project->unique_id)
        ->assertForbidden();
});

it('dispatches view modal from the credentials list for admins', function () {
    $credential = Credential::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'name' => 'List Credential',
        'username' => 'hidden-user',
    ]);

    Livewire::actingAs($this->admin)
        ->test(CredentialsList::class, ['projectUniqueId' => $this->project->unique_id])
        ->assertSee('List Credential')
        ->assertDontSee('hidden-user')
        ->call('viewCredential', uniqueId: $credential->unique_id)
        ->assertDispatched('open-credential-view', uniqueId: $credential->unique_id, projectUniqueId: $this->project->unique_id);
});
