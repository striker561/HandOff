<?php

namespace App\Policies;

use App\Models\Credential;
use App\Models\Project;
use App\Models\User;

class CredentialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isClient();
    }

    public function view(User $user, Credential $credential): bool
    {
        return $user->isAdmin() || $this->canAccessProject($user, $credential->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Credential $credential): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Credential $credential): bool
    {
        return false;
    }

    public function reveal(User $user, Credential $credential): bool
    {
        return $user->isAdmin();
    }

    private function canAccessProject(User $user, ?Project $project): bool
    {
        return (bool) $project && $user->isClient() && $project->client_unique_id === $user->unique_id;
    }
}
