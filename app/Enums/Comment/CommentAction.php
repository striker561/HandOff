<?php

namespace App\Enums\Comment;

enum CommentAction: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case MENTIONED_USERS = 'mentioned_users';
}
