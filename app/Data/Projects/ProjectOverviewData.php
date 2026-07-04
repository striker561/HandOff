<?php

namespace App\Data\Projects;

use App\Models\Deliverable;
use App\Models\Meeting;
use App\Models\Milestone;
use Illuminate\Support\Collection;

readonly class ProjectOverviewData
{
    /**
     * @param  Collection<int, Milestone>  $milestones
     * @param  Collection<int, Deliverable>  $recentDeliverables
     */
    public function __construct(
        public float $progressPercentage,
        public int $milestonesTotal,
        public int $milestonesCompleted,
        public int $milestonesInProgress,
        public int $milestonesPending,
        public int $deliverablesTotal,
        public int $deliverablesApproved,
        public int $credentialsTotal,
        public int $meetingsTotal,
        public int $meetingsUpcoming,
        public Collection $milestones,
        public Collection $recentDeliverables,
        public ?Meeting $nextMeeting,
    ) {}
}
