<?php

namespace App\Enums\Notification;

enum NotificationType: string
{
    case DELIVERABLE = 'deliverable';
    case COMMENT = 'comment';
    case MILESTONE = 'milestone';
}