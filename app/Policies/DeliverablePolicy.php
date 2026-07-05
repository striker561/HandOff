<?php

namespace App\Policies;

use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;

class DeliverablePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (! $user->isAdmin()) {
            return null;
        }

        return match ($ability) {
            'approve', 'reject', 'changeStatus' => false,
            default => null,
        };
    }

    public function viewAny(User $user): bool
    {
        return $user->isClient();
    }

    public function view(User $user, Deliverable $deliverable): bool
    {
        return $user->isAdmin() || $this->canAccessProject($user, $deliverable->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Deliverable $deliverable): bool
    {
        return $user->isAdmin() && $deliverable->status->isAgencyEditable();
    }

    public function delete(User $user, Deliverable $deliverable): bool
    {
        return $user->isAdmin() && $deliverable->status->isAgencyEditable();
    }

    public function changeStatus(User $user, Deliverable $deliverable): bool
    {
        return false;
    }

    public function submitForReview(User $user, Deliverable $deliverable): bool
    {
        return $user->isAdmin() && $deliverable->status->isAgencyEditable();
    }

    public function approve(User $user, Deliverable $deliverable): bool
    {
        return $user->isClient()
            && $deliverable->status->isClientReviewable()
            && $this->canAccessProject($user, $deliverable->project);
    }

    public function reject(User $user, Deliverable $deliverable): bool
    {
        return $user->isClient()
            && $deliverable->status->isClientReviewable()
            && $this->canAccessProject($user, $deliverable->project);
    }

    public function uploadFile(User $user, Deliverable $deliverable): bool
    {
        return $user->isAdmin() && $deliverable->status->isAgencyEditable();
    }

    private function canAccessProject(User $user, ?Project $project): bool
    {
        return (bool) $project && $user->isClient() && $project->client_unique_id === $user->unique_id;
    }
}
