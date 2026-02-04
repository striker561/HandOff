<?php

namespace App\Enums\Meeting;

enum MeetingAction: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case SCHEDULED = 'scheduled';
    case RESCHEDULED = 'rescheduled';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NOTES_ADDED = 'notes_added';
}
