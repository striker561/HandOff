<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use App\Models\Credential;

class CredentialPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isClient();
    }

    public function view(User $user, Credential $credential): bool
    {
        return $this->canAccessProject($user, $credential->project);
    }

    public function create(User $user, Project $project): bool
    {
        return false;
    }

    public function update(User $user, Credential $credential): bool
    {
        return false;
    }

    public function delete(User $user, Credential $credential): bool
    {
        return false;
    }

    public function reveal(User $user, Credential $credential): bool
    {
        return false;
    }

    private function canAccessProject(User $user, ?Project $project): bool
    {
        return (bool) $project && $user->isClient() && $project->client_unique_id === $user->unique_id;
    }
}
