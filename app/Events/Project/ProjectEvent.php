<?php

namespace App\Events\Project;

use App\Enums\Project\ProjectAction;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Project $project,
        public ProjectAction $action,
        public User $performedBy,
        public array $metadata = []
    ) {}
}
