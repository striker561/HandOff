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

    public function label(): string
    {
        return match ($this) {
            self::LOGIN => __('Login'),
            self::API_KEY => __('API key'),
            self::SSH_KEY => __('SSH key'),
            self::DATABASE => __('Database'),
            self::FTP => __('FTP'),
            self::OTHER => __('Other'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::LOGIN => 'blue',
            self::API_KEY => 'amber',
            self::SSH_KEY => 'lime',
            self::DATABASE => 'purple',
            self::FTP => 'gray',
            self::OTHER => 'zinc',
        };
    }
}
