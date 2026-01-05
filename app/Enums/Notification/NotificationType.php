<?php

namespace App\Enums\Notification;

enum NotificationType: string
{
    case INFO = 'info';
    case DANGER = 'danger';
    case WARNING = 'warning';
    case SUCCESS = 'success';
}