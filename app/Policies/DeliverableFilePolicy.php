<?php

namespace App\Policies;

use App\Models\DeliverableFile;
use App\Models\Project;
use App\Models\User;

class DeliverableFilePolicy
{
    public function download(User $user, DeliverableFile $file): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $deliverable = $file->deliverable;

        if ($deliverable === null || ! $this->canAccessProject($user, $deliverable->project)) {
            return false;
        }

        return $deliverable->status->isClientFileAccessible();
    }

    public function delete(User $user, DeliverableFile $file): bool
    {
        $deliverable = $file->deliverable;

        return $user->isAdmin()
            && $deliverable !== null
            && $deliverable->status->isAgencyEditable();
    }

    private function canAccessProject(User $user, ?Project $project): bool
    {
        return (bool) $project && $user->isClient() && $project->client_unique_id === $user->unique_id;
    }
}
