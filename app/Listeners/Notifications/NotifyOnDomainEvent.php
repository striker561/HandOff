<?php

namespace App\Listeners\Notifications;


use App\Enums\Meeting\MeetingAction;
use App\Enums\Comment\CommentAction;
use App\Enums\Milestone\MilestoneAction;
use App\Enums\Deliverable\DeliverableAction;

use App\Events\User\ClientEvent;
use App\Events\Meeting\MeetingEvent;
use App\Events\Comment\CommentEvent;
use App\Events\Project\ProjectEvent;
use App\Events\Milestone\MilestoneEvent;
use App\Events\Credential\CredentialEvent;
use App\Events\Deliverable\DeliverableEvent;

use App\Services\NotificationService;

use App\Models\User;

use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyOnDomainEvent implements ShouldQueue
{
    public function __construct(
        protected NotificationService $notifications
    ) {
    }

    public function handle(
        ProjectEvent|MilestoneEvent|DeliverableEvent|MeetingEvent|CredentialEvent|CommentEvent|ClientEvent $event
    ): void {
        match (true) {
            $event instanceof DeliverableEvent => $this->handleDeliverable($event),
            $event instanceof MilestoneEvent => $this->handleMilestone($event),
            $event instanceof MeetingEvent => $this->handleMeeting($event),
            $event instanceof CommentEvent => $this->handleComment($event),
            default => null,
        };
    }

    private function handleDeliverable(DeliverableEvent $event): void
    {
        $deliverable = $event->deliverable;
        $client = $deliverable->project->client;

        if (!$client) {
            return;
        }

        match ($event->action) {
            DeliverableAction::APPROVED => $this->notifications->notifyDeliverableApproved(
                deliverable: $deliverable,
                recipient: $client,
                approver: $event->performedBy
            ),
            DeliverableAction::REJECTED => $this->notifications->notifyDeliverableRejected(
                deliverable: $deliverable,
                recipient: $client,
                rejectedBy: $event->performedBy,
                feedback: $event->metadata['feedback'] ?? null
            ),
            default => null,
        };
    }

    private function handleMilestone(MilestoneEvent $event): void
    {
        if ($event->action !== MilestoneAction::COMPLETED) {
            return;
        }

        $milestone = $event->milestone;
        $client = $milestone->project->client;

        if (!$client) {
            return;
        }

        $this->notifications->notifyMilestoneCompleted(
            milestone: $milestone,
            recipient: $client
        );
    }

    private function handleMeeting(MeetingEvent $event): void
    {
        if (!in_array($event->action, [MeetingAction::SCHEDULED, MeetingAction::RESCHEDULED], true)) {
            return;
        }

        $meeting = $event->meeting;
        $client = $meeting->project->client;

        if (!$client) {
            return;
        }

        match ($event->action) {
            MeetingAction::SCHEDULED => $this->notifications->notifyMeetingScheduled(
                meeting: $meeting,
                recipient: $client,
                scheduledBy: $event->performedBy
            ),
            MeetingAction::RESCHEDULED => $this->notifications->notifyMeetingRescheduled(
                meeting: $meeting,
                recipient: $client,
                rescheduledBy: $event->performedBy
            ),
            default => null,
        };
    }

    private function handleComment(CommentEvent $event): void
    {
        if ($event->action !== CommentAction::MENTIONED_USERS) {
            return;
        }

        $comment = $event->comment;
        $mentioned = $comment->mentioned_users ?? $event->metadata['mentioned_users'] ?? [];

        if (!is_array($mentioned) || $mentioned === []) {
            return;
        }

        $users = User::query()
            ->whereIn('unique_id', $mentioned)
            ->get();

        foreach ($users as $user) {
            if ($user->unique_id === $event->performedBy->unique_id) {
                continue;
            }

            $this->notifications->notifyCommentMention(
                comment: $comment,
                mentionedUser: $user,
                commenter: $event->performedBy
            );
        }
    }
}
