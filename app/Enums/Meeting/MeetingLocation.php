<?php

namespace App\Enums\Meeting;

enum MeetingLocation: string
{
    case ZOOM = 'zoom';
    case MEET = 'meet';
    case TEAMS = 'teams';
    case PHYSICAL = 'physical';

    public function label(): string
    {
        return match ($this) {
            self::ZOOM => __('Zoom'),
            self::MEET => __('Google Meet'),
            self::TEAMS => __('Microsoft Teams'),
            self::PHYSICAL => __('In person'),
        };
    }
}
