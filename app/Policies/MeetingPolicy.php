<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;

class MeetingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isClient();
    }

    public function view(User $user, Meeting $meeting): bool
    {
        return $user->isAdmin() || $this->canAccessProject($user, $meeting->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $user->isAdmin() || $this->canAccessProject($user, $project);
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $user->isAdmin() || $this->canAccessProject($user, $meeting->project);
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return false;
    }

    public function reschedule(User $user, Meeting $meeting): bool
    {
        return $user->isAdmin() || $this->canAccessProject($user, $meeting->project);
    }

    public function cancel(User $user, Meeting $meeting): bool
    {
        return $user->isAdmin() || $this->canAccessProject($user, $meeting->project);
    }

    public function addNotes(User $user, Meeting $meeting): bool
    {
        return $user->isAdmin() || $this->canAccessProject($user, $meeting->project);
    }

    private function canAccessProject(User $user, ?Project $project): bool
    {
        return (bool) $project && $user->isClient() && $project->client_unique_id === $user->unique_id;
    }
}
