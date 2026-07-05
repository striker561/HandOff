<?php

namespace Tests\Feature\Http\Middleware;

use App\Enums\User\AccountRole;
use App\Http\Middleware\EnsureProjectAccess;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

function projectHubRequest(string $projectUniqueId): Request
{
    $request = Request::create("/agency/projects/{$projectUniqueId}", 'GET');

    $route = new Route('GET', '/agency/projects/{projectUniqueId}', []);
    $route->bind($request);

    $request->setRouteResolver(fn () => $route);

    return $request;
}

it('attaches the authorized project to the request', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    $this->actingAs($admin);

    $request = projectHubRequest($project->unique_id);
    $middleware = new EnsureProjectAccess;

    $middleware->handle($request, fn () => response('ok'));

    expect($request->attributes->get(EnsureProjectAccess::PROJECT_ATTRIBUTE))
        ->toBeInstanceOf(Project::class)
        ->unique_id->toBe($project->unique_id);
});

it('returns not found when the project does not exist', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin);

    $request = projectHubRequest(Str::uuid()->toString());
    $middleware = new EnsureProjectAccess;

    $middleware->handle($request, fn () => response('ok'));
})->throws(NotFoundHttpException::class);

it('forbids users who fail the project view policy', function () {
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $otherClient = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $otherClient->unique_id]);

    $this->actingAs($client);

    $request = projectHubRequest($project->unique_id);
    $middleware = new EnsureProjectAccess;

    $middleware->handle($request, fn () => response('ok'));
})->throws(AuthorizationException::class);
