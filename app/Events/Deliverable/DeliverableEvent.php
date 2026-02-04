<?php

namespace App\Events\Deliverable;

use App\Enums\Deliverable\DeliverableAction;
use App\Models\Deliverable;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliverableEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Deliverable $deliverable,
        public DeliverableAction $action,
        public User $performedBy,
        public array $metadata = []
    ) {}
}
