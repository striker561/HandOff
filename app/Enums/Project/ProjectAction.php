<?php

namespace App\Enums\Project;

enum ProjectAction: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case STATUS_CHANGED = 'status_changed';
    case PROGRESS_UPDATED = 'progress_updated';
}
