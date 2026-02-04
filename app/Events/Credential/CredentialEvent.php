<?php

namespace App\Events\Credential;

use App\Enums\Credential\CredentialAction;
use App\Models\Credential;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CredentialEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Credential $credential,
        public CredentialAction $action,
        public User $performedBy,
        public array $metadata = []
    ) {}
}
