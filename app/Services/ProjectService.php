<?php

namespace App\Services;

use App\Models\Project;
use App\Enums\Project\ProjectStatus;
use App\Enums\Project\ProjectAction;
use App\Events\Project\ProjectEvent;
use App\Models\User;

class ProjectService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return Project::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'description'];
    }

    protected function filterableColumns(): array
    {
        return ['status', 'client_unique_id'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'status', 'budget', 'due_date', 'created_at', 'updated_at'];
    }

    public function calculateProgress(Project $project): float
    {
        $milestones = $project->milestones;

        if ($milestones->isEmpty()) {
            return 0;
        }

        $completedCount = $milestones->where(
            'is_completed',
            true
        )->count();

        return round(($completedCount / $milestones->count()) * 100, 2);
    }

    public function changeStatus(Project $project, ProjectStatus $status, User $performedBy): Project
    {
        $fromStatus = $project->status;
        $project->update(
            ['status' => $status]
        );

        ProjectEvent::dispatch(
            $project,
            ProjectAction::STATUS_CHANGED,
            $performedBy,
            [
                'from_status' => $fromStatus?->value,
                'to_status' => $status->value,
            ]
        );

        return $project->fresh();
    }
}
