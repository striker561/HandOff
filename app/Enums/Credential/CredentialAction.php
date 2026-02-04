<?php

namespace App\Enums\Credential;

enum CredentialAction: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case ACCESSED = 'accessed';
}
