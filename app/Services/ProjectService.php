<?php

namespace App\Services;

use App\Models\Project;
use App\Enums\Project\ProjectStatus;

class ProjectService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return Project::class;
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

    public function changeStatus(Project $project, ProjectStatus $status): Project
    {
        $project->update(
            ['status' => $status]
        );
        return $project->fresh();
    }
}
