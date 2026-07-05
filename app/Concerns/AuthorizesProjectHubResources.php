<?php

namespace App\Concerns;

use App\Services\ProjectService;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesProjectHubResources
{
    /**
     * Authorize viewing a project-scoped resource when opening a modal.
     */
    protected function viewHubResource(
        ?string $uniqueId,
        string $projectUniqueId,
        callable $find,
    ): ?object {
        if ($uniqueId === null) {
            return null;
        }

        $resource = $find($uniqueId, $projectUniqueId);

        if ($resource === null) {
            return null;
        }

        $this->authorize('view', $resource);

        return $resource;
    }

    /**
     * Find and authorize a project-scoped resource for a list or modal action.
     */
    protected function authorizeHubResource(
        string $ability,
        string $uniqueId,
        string $projectUniqueId,
        callable $find,
    ): ?object {
        $resource = $find($uniqueId, $projectUniqueId);

        if ($resource === null) {
            return null;
        }

        $this->authorize($ability, $resource);

        return $resource;
    }

    /**
     * Authorize creating a project-scoped resource before save.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function authorizeHubResourceCreate(
        string $modelClass,
        string $projectUniqueId,
        ProjectService $projectService,
    ): bool {
        $project = $projectService->findProject($projectUniqueId);

        if ($project === null) {
            return false;
        }

        $this->authorize('create', [$modelClass, $project]);

        return true;
    }
}
