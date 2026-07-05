<?php

namespace App\Enums\Project;

enum ProjectStatus: string
{
    case PLANNING = 'planning';
    case ACTIVE = 'active';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PLANNING => __('Planning'),
            self::ACTIVE => __('Active'),
            self::ON_HOLD => __('On hold'),
            self::COMPLETED => __('Completed'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PLANNING => 'gray',
            self::ACTIVE => 'blue',
            self::ON_HOLD => 'amber',
            self::COMPLETED => 'lime',
            self::CANCELLED => 'red',
        };
    }
}
