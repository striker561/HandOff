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

    public function view(User $actor, User $client): bool
    {
        return $actor->isAdmin() && $client->isClient();
    }

    public function create(User $user): bool
    {
        return $user->role == AccountRole::ADMIN;
    }

    public function resendInvitation(User $actor, User $client): bool
    {
        return $actor->isAdmin()
            && $client->isClient()
            && $client->email_verified_at === null;
    }
}
