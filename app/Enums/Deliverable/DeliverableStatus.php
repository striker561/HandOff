<?php

namespace App\Enums\Deliverable;

enum DeliverableStatus: string
{
    case DRAFT = 'draft';
    case FINAL = 'final';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_REVIEW = 'in_review';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => __('Draft'),
            self::FINAL => __('Final'),
            self::APPROVED => __('Approved'),
            self::REJECTED => __('Rejected'),
            self::IN_REVIEW => __('In review'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::FINAL => 'blue',
            self::APPROVED => 'lime',
            self::REJECTED => 'red',
            self::IN_REVIEW => 'amber',
        };
    }

    public function isAgencyEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::REJECTED], true);
    }

    public function isClientReviewable(): bool
    {
        return $this === self::IN_REVIEW;
    }
}
