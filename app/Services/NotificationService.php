<?php

namespace App\Services;

use App\Models\{Notification, User};
use App\Enums\Notification\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return Notification::class;
    }

    protected function searchableColumns(): array
    {
        return []; // No searchable text fields
    }

    protected function filterableColumns(): array
    {
        return ['user_unique_id', 'type', 'notifiable_type', 'notifiable_id'];
    }

    protected function sortableColumns(): array
    {
        return ['created_at', 'updated_at', 'read_at'];
    }


    public function notifyMultipleUsers(
        array $users,
        NotificationType $type,
        Model $notifiable,
        array $data = []
    ): int {
        $count = 0;

        foreach ($users as $user) {
            if ($user instanceof User) {
                $this->createNotification($user, $type, $notifiable, $data);
                $count++;
            }
        }

        return $count;
    }

    public function getUserNotifications(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Notification::query()->where('user_unique_id', $user->unique_id);
        $query = $this->applyFilters($query, $filters);
        return $this->paginateQuery($query, $filters);
    }

    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_unique_id', $user->unique_id)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->markAsRead();
        return $notification->fresh();
    }

    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_unique_id', $user->unique_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function deleteNotification(Notification $notification): bool
    {
        return $notification->delete();
    }

    public function clearAllNotifications(User $user): int
    {
        return Notification::where('user_unique_id', $user->unique_id)->delete();
    }

    public function notifyDeliverableApproved(
        Model $deliverable,
        User $recipient,
        ?User $approver = null
    ): Notification {
        return $this->createNotification(
            user: $recipient,
            type: NotificationType::DELIVERABLE,
            notifiable: $deliverable,
            data: [
                'message' => 'Your deliverable has been approved',
                'approved_by' => $approver?->name,
            ]
        );
    }

    public function notifyDeliverableRejected(
        Model $deliverable,
        User $recipient,
        User $rejectedBy,
        ?string $feedback = null
    ): Notification {
        return $this->createNotification(
            user: $recipient,
            type: NotificationType::DELIVERABLE,
            notifiable: $deliverable,
            data: [
                'message' => 'Your deliverable has been rejected',
                'rejected_by' => $rejectedBy->name,
                'feedback' => $feedback,
            ]
        );
    }

    public function notifyCommentMention(
        Model $comment,
        User $mentionedUser,
        User $commenter
    ): Notification {
        return $this->createNotification(
            user: $mentionedUser,
            type: NotificationType::COMMENT,
            notifiable: $comment,
            data: [
                'message' => "{$commenter->name} mentioned you in a comment",
                'commenter' => $commenter->name,
            ]
        );
    }

    public function notifyMilestoneCompleted(
        Model $milestone,
        User $recipient
    ): Notification {
        return $this->createNotification(
            user: $recipient,
            type: NotificationType::MILESTONE,
            notifiable: $milestone,
            data: [
                'message' => 'A milestone has been completed',
            ]
        );
    }

    public function notifyMeetingScheduled(
        Model $meeting,
        User $recipient,
        ?User $scheduledBy = null
    ): Notification {
        return $this->createNotification(
            user: $recipient,
            type: NotificationType::MEETING,
            notifiable: $meeting,
            data: [
                'message' => 'A meeting has been scheduled',
                'scheduled_by' => $scheduledBy?->name,
            ]
        );
    }

    public function notifyMeetingRescheduled(
        Model $meeting,
        User $recipient,
        ?User $rescheduledBy = null
    ): Notification {
        return $this->createNotification(
            user: $recipient,
            type: NotificationType::MEETING,
            notifiable: $meeting,
            data: [
                'message' => 'A meeting has been rescheduled',
                'rescheduled_by' => $rescheduledBy?->name,
            ]
        );
    }


    private function createNotification(
        User $user,
        NotificationType $type,
        Model $notifiable,
        array $data = []
    ): Notification {
        /** @var Notification $notification */
        $notification = Notification::create([
            'user_unique_id' => $user->unique_id,
            'type' => $type,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->unique_id,
            'data' => $data,
        ]);

        return $notification;
    }
}
