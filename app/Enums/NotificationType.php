<?php

namespace App\Enums;

enum NotificationType: string
{
    case INFO = 'info';
    case DANGER = 'danger';
    case WARNING = 'warning';
    case SUCCESS = 'success';
}