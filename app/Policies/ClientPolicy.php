<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\User\AccountRole;

class ClientPolicy
{
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
