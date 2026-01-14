<?php

namespace App\Services;

use App\Models\Milestone;
use App\Enums\Milestone\MilestoneStatus;

class MilestoneService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return Milestone::class;
    }

    public function createOrderedMilestone(array $data): Milestone
    {
        /** @var Milestone $milestone */
        $milestone = $this->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'project_id' => $data['project_id'],
            'due_date' => $data['due_date'] ?? null,
            'order' => $this->getNextOrder($data['project_id']),
            'is_completed' => false,
        ]);

        return $milestone;
    }

    public function updateStatus(
        Milestone $milestone,
        MilestoneStatus $status
    ): Milestone {
        $completedAt = $status === MilestoneStatus::COMPLETED ? now() : null;
        $milestone->update([
            'status' => $status,
            'completed_at' => $completedAt,
        ]);

        return $milestone->fresh();
    }

    public function reorder(
        string $projectUniqueId,
        array $milestoneUniqueIds
    ): void
    {
        foreach ($milestoneUniqueIds as $index => $uniqueId) {
            Milestone::where('project_unique_id', $projectUniqueId)
                ->where('unique_id', $uniqueId)
                ->update(['order' => $index + 1]);
        }
    }

    private function getNextOrder(string $projectUniqueId): int
    {
        return (int) Milestone::where('project_unique_id', $projectUniqueId)
            ->lockForUpdate()
            ->max('order') + 1;
    }
}
