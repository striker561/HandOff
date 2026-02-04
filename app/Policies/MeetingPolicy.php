<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use App\Models\Meeting;

class MeetingPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isClient();
    }

    public function view(User $user, Meeting $meeting): bool
    {
        return $this->canAccessProject($user, $meeting->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->canAccessProject($user, $project);
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $this->canAccessProject($user, $meeting->project);
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return false;
    }

    public function reschedule(User $user, Meeting $meeting): bool
    {
        return $this->canAccessProject($user, $meeting->project);
    }

    public function cancel(User $user, Meeting $meeting): bool
    {
        return $this->canAccessProject($user, $meeting->project);
    }

    public function addNotes(User $user, Meeting $meeting): bool
    {
        return $this->canAccessProject($user, $meeting->project);
    }

    private function canAccessProject(User $user, ?Project $project): bool
    {
        return (bool) $project && $user->isClient() && $project->client_unique_id === $user->unique_id;
    }
}
