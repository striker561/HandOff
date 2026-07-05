<?php

use App\Enums\User\AccountRole;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;

/**
 * @return array{admin: User, client: User, project: Project}
 */
function projectHubActors(): array
{
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    return [
        'admin' => User::factory()->create(['role' => AccountRole::ADMIN]),
        'client' => $client,
        'project' => $project,
    ];
}

function bindProjectHubAuthorizationContext(): void
{
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    test()->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    test()->client = $client;
    test()->otherClient = User::factory()->create(['role' => AccountRole::CLIENT]);
    test()->project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    test()->milestone = Milestone::factory()->create([
        'project_unique_id' => test()->project->unique_id,
        'order' => 1,
    ]);
}
