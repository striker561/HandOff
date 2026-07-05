<?php

namespace App\Services;

use App\Data\Milestones\SaveMilestoneData;
use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Milestone\MilestoneAction;
use App\Enums\Milestone\MilestoneStatus;
use App\Events\Milestone\MilestoneEvent;
use App\Models\Milestone;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class MilestoneService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return Milestone::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'description'];
    }

    protected function filterableColumns(): array
    {
        return ['project_unique_id', 'status', 'is_completed'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'order', 'due_date', 'created_at', 'updated_at', 'completed_at'];
    }

    public function findMilestoneForProject(string $uniqueId, string $projectUniqueId): ?Milestone
    {
        return Milestone::query()
            ->where('unique_id', $uniqueId)
            ->where('project_unique_id', $projectUniqueId)
            ->first();
    }

    public function createOrderedMilestone(SaveMilestoneData $data, User $performedBy): Milestone
    {
        /** @var Milestone $milestone */
        $milestone = $this->create($data->toCreateAttributes(
            $this->getNextOrder($data->projectUniqueId)
        ));

        MilestoneEvent::dispatch(
            $milestone,
            MilestoneAction::CREATED,
            $performedBy,
            []
        );

        return $milestone;
    }

    public function updateMilestone(Milestone $milestone, SaveMilestoneData $data, User $performedBy): Milestone
    {
        $milestone->update($data->toUpdateAttributes());

        if ($data->status !== null && $data->status !== $milestone->status) {
            return $this->updateStatus($milestone, $data->status, $performedBy);
        }

        MilestoneEvent::dispatch(
            $milestone,
            MilestoneAction::UPDATED,
            $performedBy,
            []
        );

        return $milestone->fresh();
    }

    public function getMilestonesForProject(string $projectUniqueId, array $filters = []): LengthAwarePaginator
    {
        $query = Milestone::query()
            ->where('project_unique_id', $projectUniqueId)
            ->withCount('deliverables');
        $query = $this->applyFilters($query, $filters);

        return $this->paginateQuery($query, $filters);
    }

    public function updateStatus(
        Milestone $milestone,
        MilestoneStatus $status,
        User $performedBy,
        array $metadata = [],
    ): Milestone {
        $fromStatus = $milestone->status;

        if ($fromStatus === $status) {
            return $milestone;
        }

        $completedAt = $status === MilestoneStatus::COMPLETED ? now() : null;
        $milestone->update([
            'status' => $status,
            'completed_at' => $completedAt,
        ]);

        $action = $status === MilestoneStatus::COMPLETED
            ? MilestoneAction::COMPLETED
            : MilestoneAction::STATUS_CHANGED;

        MilestoneEvent::dispatch(
            $milestone->fresh(),
            $action,
            $performedBy,
            array_merge([
                'from_status' => $fromStatus->value,
                'to_status' => $status->value,
            ], $metadata),
        );

        return $milestone->fresh();
    }

    /**
     * Reconcile milestone completion from its deliverables (auto-complete / auto-reopen).
     */
    public function syncFromDeliverables(Milestone $milestone, User $performedBy): void
    {
        $milestone = $milestone->fresh();

        $hasDeliverables = $milestone->deliverables()->exists();

        $allApproved = $hasDeliverables && $milestone->deliverables()
            ->where('status', '!=', DeliverableStatus::APPROVED)
            ->doesntExist();

        if ($allApproved && ! $milestone->is_completed) {
            $this->updateStatus($milestone, MilestoneStatus::COMPLETED, $performedBy, [
                'auto_completed' => true,
            ]);

            return;
        }

        if (! $allApproved && $milestone->is_completed) {
            $this->updateStatus($milestone, MilestoneStatus::IN_PROGRESS, $performedBy, [
                'auto_uncompleted' => true,
            ]);
        }
    }

    public function reorder(
        string $projectUniqueId,
        array $milestoneUniqueIds,
        User $performedBy
    ): void {
        foreach ($milestoneUniqueIds as $index => $uniqueId) {
            Milestone::where('project_unique_id', $projectUniqueId)
                ->where('unique_id', $uniqueId)
                ->update(['order' => $index + 1]);
        }

        $milestone = Milestone::query()
            ->where('project_unique_id', $projectUniqueId)
            ->orderBy('order')
            ->first();

        if ($milestone) {
            MilestoneEvent::dispatch(
                $milestone,
                MilestoneAction::REORDERED,
                $performedBy,
                ['milestone_unique_ids' => $milestoneUniqueIds]
            );
        }
    }

    private function getNextOrder(string $projectUniqueId): int
    {
        return (int) Milestone::where('project_unique_id', $projectUniqueId)
            ->lockForUpdate()
            ->max('order') + 1;
    }
}
