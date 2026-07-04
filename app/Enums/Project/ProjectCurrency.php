<?php

namespace App\Enums\Project;

enum ProjectCurrency: string
{
    case USD = 'usd';
    case NGN = 'ngn';
    case EUR = 'eur';

    public function label(): string
    {
        return match ($this) {
            self::USD => __('USD ($)'),
            self::NGN => __('NGN (₦)'),
            self::EUR => __('EUR (€)'),
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::USD => '$',
            self::NGN => '₦',
            self::EUR => '€',
        };
    }
}
