<?php

namespace App\Policies;

use App\Enums\User\AccountRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role == AccountRole::ADMIN;
    }

    public function view(User $user): bool
    {
        return $user->role == AccountRole::ADMIN;
    }

    public function create(User $user): bool
    {
        return $user->role == AccountRole::ADMIN;
    }

    public function resendInvitation(User $actor, User $client): bool
    {
        $isAdmin = (bool) ($actor->role == AccountRole::ADMIN);
        $isClient = $client->role === AccountRole::CLIENT;

        return $isAdmin && $isClient;
    }
}
