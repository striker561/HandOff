<?php

namespace App\Enums\Credential;

enum CredentialType: string
{
    case LOGIN = 'login';
    case API_KEY = 'api_key';
    case SSH_KEY = 'ssh_key';
    case DATABASE = 'database';
    case FTP = 'ftp';
    case OTHER = 'other';
}
