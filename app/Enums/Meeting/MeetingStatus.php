<?php

namespace App\Enums\Meeting;

enum MeetingStatus: string
{
    case SCHEDULED = 'scheduled';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case RESCHEDULED = 'rescheduled';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => __('Scheduled'),
            self::COMPLETED => __('Completed'),
            self::CANCELLED => __('Cancelled'),
            self::RESCHEDULED => __('Rescheduled'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::SCHEDULED => 'blue',
            self::COMPLETED => 'lime',
            self::CANCELLED => 'red',
            self::RESCHEDULED => 'amber',
        };
    }
}
