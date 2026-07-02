<?php

namespace App\Listeners\ActivityLog;

use App\Events\Comment\CommentEvent;
use App\Events\Credential\CredentialEvent;
use App\Events\Deliverable\DeliverableEvent;
use App\Events\Meeting\MeetingEvent;
use App\Events\Milestone\MilestoneEvent;
use App\Events\Project\ProjectEvent;
use App\Events\User\ClientEvent;
use App\Services\ActivityLogService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogActivity implements ShouldQueue
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    public function handle(
        ProjectEvent|MilestoneEvent|DeliverableEvent|MeetingEvent|CredentialEvent|CommentEvent|ClientEvent $event
    ): void {
        $model = match (true) {
            $event instanceof ProjectEvent => $event->project,
            $event instanceof MilestoneEvent => $event->milestone,
            $event instanceof DeliverableEvent => $event->deliverable,
            $event instanceof MeetingEvent => $event->meeting,
            $event instanceof CredentialEvent => $event->credential,
            $event instanceof CommentEvent => $event->comment,
            $event instanceof ClientEvent => $event->client,
        };

        $this->activityLog->log(
            $model,
            $event->action->value,
            $event->performedBy,
            $event->metadata
        );
    }
}
