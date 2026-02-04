<?php

namespace App\Enums\User;

enum ClientAction: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case INVITATION_SENT = 'invitation_sent';
}
