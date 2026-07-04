<?php

namespace App\Enums\Milestone;

enum MilestoneStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::IN_PROGRESS => __('In progress'),
            self::COMPLETED => __('Completed'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::IN_PROGRESS => 'blue',
            self::COMPLETED => 'lime',
        };
    }
}
