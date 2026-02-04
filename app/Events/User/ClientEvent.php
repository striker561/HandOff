<?php

namespace App\Events\User;

use App\Enums\User\ClientAction;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $client,
        public ClientAction $action,
        public User $performedBy,
        public array $metadata = []
    ) {}
}
