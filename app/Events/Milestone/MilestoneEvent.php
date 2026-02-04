<?php

namespace App\Events\Milestone;

use App\Enums\Milestone\MilestoneAction;
use App\Models\Milestone;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MilestoneEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Milestone $milestone,
        public MilestoneAction $action,
        public User $performedBy,
        public array $metadata = []
    ) {}
}
