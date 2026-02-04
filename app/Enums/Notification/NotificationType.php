<?php

namespace App\Enums\Notification;

enum NotificationType: string
{
    case INFO = 'info';
    case DANGER = 'danger';
    case WARNING = 'warning';
    case SUCCESS = 'success';
    case DELIVERABLE_APPROVED = 'deliverable_approved';
    case COMMENT_MENTION = 'comment_mention';
    case MILESTONE_COMPLETED = 'milestone_completed';
}