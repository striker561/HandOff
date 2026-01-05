<?php

namespace App\Enums\User;

enum AccountRole: string
{
    case CLIENT = 'client';
    case ADMIN = 'admin';
}