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

    public function log(
        Model $subject,
        string $action,
        ?User $causer = null,
        array $properties = []
    ): ActivityLog {
        $logName = match ($action) {
            'created', 'scheduled' => LogName::CREATED,
            'updated' => LogName::UPDATED,
            'deleted' => LogName::DELETED,
            'file_uploaded', 'file_downloaded', 'file_deleted' => LogName::FILE,
            default => LogName::DEFAULT ,
        };

        $description = class_basename($subject) . ' ' . str_replace('_', ' ', $action);

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
