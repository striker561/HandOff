<?php

namespace App\Events\Meeting;

use App\Enums\Meeting\MeetingAction;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Meeting $meeting,
        public MeetingAction $action,
        public User $performedBy,
        public array $metadata = []
    ) {}
}
