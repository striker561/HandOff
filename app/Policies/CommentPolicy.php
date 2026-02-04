<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use App\Models\Comment;
use App\Models\Milestone;
use App\Models\Deliverable;

class CommentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isClient();
    }

    public function view(User $user, Comment $comment): bool
    {
        if ($comment->is_internal) {
            return false;
        }

        return $this->canAccessProject($user, $this->projectForComment($comment));
    }

    public function create(User $user, mixed $commentable): bool
    {
        $project = match (true) {
            $commentable instanceof Project => $commentable,
            $commentable instanceof Milestone => $commentable->project,
            $commentable instanceof Deliverable => $commentable->project,
            default => null,
        };

        return $this->canAccessProject($user, $project);
    }

    public function update(User $user, Comment $comment): bool
    {
        if ($comment->is_internal) {
            return false;
        }

        return $user->unique_id === $comment->user_unique_id
            && $this->canAccessProject($user, $this->projectForComment($comment));
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->unique_id === $comment->user_unique_id
            && $this->canAccessProject($user, $this->projectForComment($comment));
    }

    public function createInternal(User $user): bool
    {
        return false;
    }

    public function viewInternal(User $user): bool
    {
        return false;
    }

    private function canAccessProject(User $user, ?Project $project): bool
    {
        return (bool) $project && $user->isClient() && $project->client_unique_id === $user->unique_id;
    }

    private function projectForComment(Comment $comment): ?Project
    {
        $commentable = $comment->commentable;

        return match (true) {
            $commentable instanceof Project => $commentable,
            $commentable instanceof Milestone => $commentable->project,
            $commentable instanceof Deliverable => $commentable->project,
            default => null,
        };
    }
}
