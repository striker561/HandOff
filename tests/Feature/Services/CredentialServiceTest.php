<?php

use App\Data\Credentials\SaveCredentialData;
use App\Enums\Credential\CredentialAction;
use App\Enums\Credential\CredentialType;
use App\Enums\User\AccountRole;
use App\Events\Credential\CredentialEvent;
use App\Models\Credential;
use App\Models\Project;
use App\Models\User;
use App\Services\CredentialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(CredentialService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->project = Project::factory()->create();
});

it('creates a credential with encrypted sensitive fields', function () {
    $credential = $this->service->createCredential(SaveCredentialData::fromArray([
        'project_unique_id' => $this->project->unique_id,
        'name' => 'Production DB',
        'type' => CredentialType::DATABASE->value,
        'username' => 'root',
        'password' => 'secret-password',
        'url' => 'https://db.example.com',
        'notes' => 'Primary cluster',
    ]), $this->admin);

    expect($credential)->toBeInstanceOf(Credential::class)
        ->and($credential->name)->toBe('Production DB')
        ->and($credential->username)->toBe('root')
        ->and($credential->password)->toBe('secret-password');

    $raw = DB::table('credentials')->where('unique_id', $credential->unique_id)->first();

    expect($raw->username)->not->toBe('root')
        ->and($raw->password)->not->toBe('secret-password')
        ->and($raw->url)->not->toBe('https://db.example.com')
        ->and($raw->notes)->not->toBe('Primary cluster');
});

it('updates a credential without changing password when blank', function () {
    Event::fake([CredentialEvent::class]);

    $credential = Credential::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'password' => 'keep-me',
    ]);

    $updated = $this->service->updateCredential($credential, SaveCredentialData::fromArray([
        'project_unique_id' => $this->project->unique_id,
        'name' => 'Renamed DB',
        'type' => CredentialType::DATABASE->value,
    ]), $this->admin);

    expect($updated->name)->toBe('Renamed DB')
        ->and($updated->password)->toBe('keep-me');

    Event::assertDispatched(CredentialEvent::class, function (CredentialEvent $event) use ($credential) {
        return $event->action === CredentialAction::UPDATED
            && $event->credential->is($credential);
    });
});

it('reveals a credential', function () {
    $credential = Credential::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'username' => 'deploy',
        'password' => 'secret',
    ]);

    $revealed = $this->service->revealCredential($credential, $this->admin);

    expect($revealed)->toHaveKeys(['password', 'username', 'url', 'notes'])
        ->and($revealed['username'])->toBe('deploy')
        ->and($revealed['password'])->toBe('secret');
});

it('gets credentials for a project without selecting encrypted columns', function () {
    Credential::factory()->count(3)->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    $result = $this->service->getCredentialsForProject($this->project->unique_id);

    expect($result->total())->toBe(3)
        ->and($result->first()->username)->toBeNull();
});
