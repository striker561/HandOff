<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use App\Models\Milestone;

class MilestonePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isClient();
    }

    public function view(User $user, Milestone $milestone): bool
    {
        return $this->canAccessProject($user, $milestone->project);
    }

    public function create(User $user, Project $project): bool
    {
        return false;
    }

    public function update(User $user, Milestone $milestone): bool
    {
        return false;
    }

    public function delete(User $user, Milestone $milestone): bool
    {
        return false;
    }

    public function updateStatus(User $user, Milestone $milestone): bool
    {
        return false;
    }

    public function reorder(User $user, Project $project): bool
    {
        return false;
    }

    private function canAccessProject(User $user, ?Project $project): bool
    {
        return (bool) $project && $user->isClient() && $project->client_unique_id === $user->unique_id;
    }
}
