<?php

namespace App\Services;

use App\Enums\ActivityLog\LogName;
use App\Models\{ActivityLog, User};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return ActivityLog::class;
    }

    protected function searchableColumns(): array
    {
        return ['description'];
    }

    protected function filterableColumns(): array
    {
        return ['log_name', 'user_unique_id', 'subject_type', 'subject_id', 'causer_type', 'causer_id'];
    }

    protected function sortableColumns(): array
    {
        return ['created_at', 'updated_at'];
    }

    public function logCreated(Model $subject, ?User $causer = null, array $properties = []): ActivityLog
    {
        return $this->log(
            logName: LogName::DEFAULT ,
            description: class_basename($subject) . ' created',
            subject: $subject,
            causer: $causer,
            properties: $properties
        );
    }

    public function logUpdated(Model $subject, ?User $causer = null, array $changes = []): ActivityLog
    {
        return $this->log(
            logName: LogName::DEFAULT ,
            description: class_basename($subject) . ' updated',
            subject: $subject,
            causer: $causer,
            properties: ['changes' => $changes]
        );
    }

    public function logDeleted(Model $subject, ?User $causer = null): ActivityLog
    {
        return $this->log(
            logName: LogName::DEFAULT ,
            description: class_basename($subject) . ' deleted',
            subject: $subject,
            causer: $causer
        );
    }

    public function logStatusChanged(
        Model $subject,
        string $oldStatus,
        string $newStatus,
        ?User $causer = null
    ): ActivityLog {
        return $this->log(
            logName: LogName::DEFAULT ,
            description: class_basename($subject) . " status changed from {$oldStatus} to {$newStatus}",
            subject: $subject,
            causer: $causer,
            properties: [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        );
    }

    public function logFileUploaded(
        Model $subject,
        string $filename,
        ?User $causer = null
    ): ActivityLog {
        return $this->log(
            logName: LogName::FILE,
            description: "File '{$filename}' uploaded to " . class_basename($subject),
            subject: $subject,
            causer: $causer,
            properties: ['filename' => $filename]
        );
    }

    public function logCommentAdded(
        Model $subject,
        string $commentBody,
        ?User $causer = null
    ): ActivityLog {
        return $this->log(
            logName: LogName::COMMENT,
            description: 'Comment added to ' . class_basename($subject),
            subject: $subject,
            causer: $causer,
            properties: ['comment_preview' => substr($commentBody, 0, 100)]
        );
    }

    public function getActivitiesForSubject(Model $subject, array $filters = []): LengthAwarePaginator
    {
        $query = ActivityLog::query()
            ->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->unique_id);
        $query = $this->applyFilters($query, $filters);
        return $this->paginateQuery($query, $filters);
    }

    public function getActivitiesForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = ActivityLog::query()->where('user_unique_id', $user->unique_id);
        $query = $this->applyFilters($query, $filters);
        return $this->paginateQuery($query, $filters);
    }

    private function log(
        LogName $logName,
        string $description,
        Model $subject,
        ?User $causer = null,
        array $properties = []
    ): ActivityLog {
        /** @var ActivityLog $log */
        $log = $this->create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->unique_id,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer?->unique_id,
            'user_unique_id' => $causer?->unique_id,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
        return $log;
    }
}
