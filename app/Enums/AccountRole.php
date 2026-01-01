<?php

namespace App\Enums;

enum AccountRole: string
{
    case CLIENT = 'client';
    case ADMIN = 'admin';
}