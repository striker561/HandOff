<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use App\Models\Deliverable;
use App\Models\DeliverableFile;

class DeliverablePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isClient();
    }

    public function view(User $user, Deliverable $deliverable): bool
    {
        return $this->canAccessProject($user, $deliverable->project);
    }

    public function create(User $user, Project $project): bool
    {
        return false;
    }

    public function update(User $user, Deliverable $deliverable): bool
    {
        return false;
    }

    public function delete(User $user, Deliverable $deliverable): bool
    {
        return false;
    }

    public function changeStatus(User $user, Deliverable $deliverable): bool
    {
        return false;
    }

    public function approve(User $user, Deliverable $deliverable): bool
    {
        return $this->canAccessProject($user, $deliverable->project);
    }

    public function reject(User $user, Deliverable $deliverable): bool
    {
        return $this->canAccessProject($user, $deliverable->project);
    }

    public function uploadFile(User $user, Deliverable $deliverable): bool
    {
        return false;
    }

    public function downloadFile(User $user, DeliverableFile $file): bool
    {
        return $this->canAccessProject($user, $file->deliverable?->project);
    }

    public function deleteFile(User $user, DeliverableFile $file): bool
    {
        return false;
    }

    private function canAccessProject(User $user, ?Project $project): bool
    {
        return (bool) $project && $user->isClient() && $project->client_unique_id === $user->unique_id;
    }
}
