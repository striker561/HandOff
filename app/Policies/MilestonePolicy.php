<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;

class MilestonePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isClient();
    }

    public function view(User $user, Milestone $milestone): bool
    {
        return $user->isAdmin() || $this->canAccessProject($user, $milestone->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Milestone $milestone): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Milestone $milestone): bool
    {
        return false;
    }

    public function updateStatus(User $user, Milestone $milestone): bool
    {
        return $user->isAdmin();
    }

    public function reorder(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    private function canAccessProject(User $user, ?Project $project): bool
    {
        return (bool) $project && $user->isClient() && $project->client_unique_id === $user->unique_id;
    }
}
