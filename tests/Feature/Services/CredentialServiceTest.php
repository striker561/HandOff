<?php

use App\Enums\Credential\CredentialType;
use App\Enums\User\AccountRole;
use App\Models\Credential;
use App\Models\Project;
use App\Models\User;
use App\Services\CredentialService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(CredentialService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->project = Project::factory()->create();
});

it('creates a credential', function () {
    $credential = $this->service->createCredential([
        'project_unique_id' => $this->project->unique_id,
        'name' => 'Production DB',
        'type' => CredentialType::DATABASE,
        'username' => 'root',
        'password' => 'secret-password',
    ], $this->admin);

    expect($credential)->toBeInstanceOf(Credential::class)
        ->and($credential->name)->toBe('Production DB');
});

it('reveals a credential', function () {
    $credential = Credential::factory()->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    $revealed = $this->service->revealCredential($credential, $this->admin);

    expect($revealed)->toHaveKey('password')
        ->and($revealed)->toHaveKey('username');
});

it('gets credentials for a project', function () {
    Credential::factory()->count(3)->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    $result = $this->service->getCredentialsForProject($this->project->unique_id);

    expect($result->total())->toBe(3);
});
