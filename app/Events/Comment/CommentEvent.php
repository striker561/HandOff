<?php

namespace App\Events\Comment;

use App\Enums\Comment\CommentAction;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Comment $comment,
        public CommentAction $action,
        public User $performedBy,
        public array $metadata = []
    ) {}
}
