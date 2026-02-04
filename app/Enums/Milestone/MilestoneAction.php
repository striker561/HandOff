<?php

namespace App\Enums\Milestone;

enum MilestoneAction: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case STATUS_CHANGED = 'status_changed';
    case COMPLETED = 'completed';
    case REORDERED = 'reordered';
}
