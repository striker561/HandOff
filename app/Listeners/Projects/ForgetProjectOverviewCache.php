<?php

namespace App\Listeners\Projects;

use App\Enums\Credential\CredentialAction;
use App\Events\Credential\CredentialEvent;
use App\Events\Deliverable\DeliverableEvent;
use App\Events\Meeting\MeetingEvent;
use App\Events\Milestone\MilestoneEvent;
use App\Services\ProjectService;

class ForgetProjectOverviewCache
{
    public function __construct(private ProjectService $projects) {}

    public function handle(DeliverableEvent|CredentialEvent|MeetingEvent|MilestoneEvent $event): void
    {
        if ($event instanceof CredentialEvent && $event->action === CredentialAction::ACCESSED) {
            return;
        }

        $projectUniqueId = match (true) {
            $event instanceof DeliverableEvent => $event->deliverable->project_unique_id,
            $event instanceof CredentialEvent => $event->credential->project_unique_id,
            $event instanceof MeetingEvent => $event->meeting->project_unique_id,
            $event instanceof MilestoneEvent => $event->milestone->project_unique_id,
        };

        $this->projects->forgetProjectOverview($projectUniqueId);
    }
}
