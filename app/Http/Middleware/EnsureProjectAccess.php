<?php

namespace App\Http\Middleware;

use App\Models\Project;
use App\Services\ProjectService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve the project hub route parameter, enforce ProjectPolicy::view, and attach the model to the request.
 *
 * @see ProjectHubController Agency project hub pages expect the authorized project on this attribute.
 */
class EnsureProjectAccess
{
    public const PROJECT_ATTRIBUTE = 'project';

    public function handle(Request $request, Closure $next): Response
    {
        /** @var string|null $projectUniqueId */
        $projectUniqueId = $request->route('projectUniqueId');

        if ($projectUniqueId === null) {
            abort(404);
        }

        $project = app(ProjectService::class)->findProject($projectUniqueId);

        if ($project === null) {
            abort(404);
        }

        Gate::authorize('view', $project);

        $request->attributes->set(self::PROJECT_ATTRIBUTE, $project);

        return $next($request);
    }
}
