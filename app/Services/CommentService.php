<?php

namespace App\Services;

use App\Models\{Comment, User};
use Illuminate\Support\Collection;
use App\Enums\Comment\CommentAction;
use App\Events\Comment\CommentEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return Comment::class;
    }

    protected function searchableColumns(): array
    {
        return ['body'];
    }

    protected function filterableColumns(): array
    {
        return ['commentable_type', 'commentable_id', 'user_unique_id', 'is_internal', 'parent_unique_id'];
    }

    protected function sortableColumns(): array
    {
        return ['created_at', 'updated_at', 'read_at'];
    }

    public function createComment(
        Model $commentable,
        User $user,
        string $body,
        bool $isInternal = false,
        ?string $parentUniqueId = null,
        array $mentionedUsers = []
    ): Comment {
        /** @var Comment $comment */
        $comment = Comment::create([
            'commentable_type' => get_class($commentable),
            'commentable_id' => $commentable->unique_id,
            'user_unique_id' => $user->unique_id,
            'body' => $body,
            'is_internal' => $isInternal,
            'parent_unique_id' => $parentUniqueId,
            'mentioned_users' => $mentionedUsers,
        ]);

        CommentEvent::dispatch(
            $comment,
            CommentAction::CREATED,
            $user,
            []
        );

        if (!empty($mentionedUsers)) {
            CommentEvent::dispatch(
                $comment,
                CommentAction::MENTIONED_USERS,
                $user,
                ['mentioned_users' => $mentionedUsers]
            );
        }

        return $comment;
    }

    public function createReply(
        Comment $parentComment,
        User $user,
        string $body,
        array $mentionedUsers = []
    ): Comment {
        return $this->createComment(
            commentable: $parentComment->commentable,
            user: $user,
            body: $body,
            isInternal: $parentComment->is_internal,
            parentUniqueId: $parentComment->unique_id,
            mentionedUsers: $mentionedUsers
        );
    }

    public function getCommentsForEntity(
        Model $commentable,
        bool $includeInternal = true
    ): Collection {
        $query = Comment::where('commentable_type', get_class($commentable))
            ->where('commentable_id', $commentable->unique_id)
            ->whereNull('parent_unique_id')
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'desc');

        if (!$includeInternal) {
            $query->where('is_internal', false);
        }

        return $query->get();
    }

    public function getCommentsForEntityPaginated(
        Model $commentable,
        array $filters = []
    ): LengthAwarePaginator {
        $query = Comment::where('commentable_type', get_class($commentable))
            ->where('commentable_id', $commentable->unique_id)
            ->whereNull('parent_unique_id')
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'desc');

        $includeInternal = $filters['include_internal'] ?? true;
        if (!$includeInternal) {
            $query->where('is_internal', false);
        }

        return $this->paginateQuery($query, $filters);
    }

    public function getCommentThread(string $commentUniqueId): Collection
    {
        $comment = Comment::with(['replies.user', 'user'])
            ->findOrFail($commentUniqueId);

        // Get the root comment
        $root = $comment->parent_unique_id
            ? Comment::with(['replies.user', 'user'])->findOrFail($comment->parent_unique_id)
            : $comment;

        return collect([$root]);
    }

    public function markAsRead(Comment $comment): Comment
    {
        $comment->update(['read_at' => now()]);
        return $comment->fresh();
    }

    public function deleteComment(Comment $comment): bool
    {
        // Soft delete replies as well
        $comment->replies()->delete();
        return $comment->delete();
    }

    public function extractMentions(string $body): array
    {
        // Extract @username or @user_id patterns
        preg_match_all('/@(\w+)/', $body, $matches);
        return $matches[1] ?? [];
    }

    public function canUserSeeComment(User $user, Comment $comment): bool
    {
        // Admin can see everything
        if ($user->isAdmin()) {
            return true;
        }

        // Clients cannot see internal comments
        if ($comment->is_internal && $user->isClient()) {
            return false;
        }

        return true;
    }
}
