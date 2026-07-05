<?php

namespace App\Policies;

use App\Models\DeliverableFile;
use App\Models\Project;
use App\Models\User;

class DeliverableFilePolicy
{
    public function download(User $user, DeliverableFile $file): bool
    {
        return $user->isAdmin() || $this->canAccessProject($user, $file->deliverable?->project);
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
