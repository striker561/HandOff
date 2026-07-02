<?php

namespace App\Enums;

use App\Models\User;

enum WorkspaceType: string
{
    case Agency = 'agency';
    case Portal = 'portal';

    public static function forUser(User $user): self
    {
        return $user->isAdmin() ? self::Agency : self::Portal;
    }

    public function label(): string
    {
        return match ($this) {
            self::Agency => __('Agency'),
            self::Portal => __('Portal'),
        };
    }
}
