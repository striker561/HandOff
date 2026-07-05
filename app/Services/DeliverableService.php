<?php

namespace App\Services;

use App\Data\Deliverables\SaveDeliverableData;
use App\Enums\Deliverable\DeliverableAction;
use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Milestone\MilestoneAction;
use App\Enums\Milestone\MilestoneStatus;
use App\Events\Deliverable\DeliverableEvent;
use App\Events\Milestone\MilestoneEvent;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class DeliverableService extends BaseCRUDService
{
    public function __construct() {}

    protected function getModel(): string
    {
        return Deliverable::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'description'];
    }

    protected function filterableColumns(): array
    {
        return ['project_unique_id', 'milestone_unique_id', 'status', 'type', 'created_by_unique_id'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'status', 'type', 'order', 'version', 'due_date', 'created_at', 'updated_at', 'approved_at'];
    }

    public function createDeliverable(SaveDeliverableData $data, User $performedBy): Deliverable
    {
        $nextOrder = $this->getNextOrder(
            $data->projectUniqueId,
            $data->milestoneUniqueId
        );

        /** @var Deliverable $deliverable */
        $deliverable = $this->create(array_merge($data->toCreateAttributes($nextOrder), [
            'status' => DeliverableStatus::DRAFT,
            'version' => 1,
        ]));

        $this->updateMilestoneOnDeliverableChange($deliverable, $performedBy);

        DeliverableEvent::dispatch(
            $deliverable,
            DeliverableAction::CREATED,
            $performedBy,
            []
        );

        return $deliverable;
    }

    public function updateDeliverable(Deliverable $deliverable, SaveDeliverableData $data, User $performedBy): Deliverable
    {
        $previousMilestoneUniqueId = $deliverable->milestone_unique_id;

        $deliverable->update($data->toUpdateAttributes());

        $deliverable = $deliverable->fresh();

        if ($previousMilestoneUniqueId !== $deliverable->milestone_unique_id) {
            if ($previousMilestoneUniqueId !== null) {
                $previousMilestone = Milestone::query()
                    ->where('unique_id', $previousMilestoneUniqueId)
                    ->first();

                if ($previousMilestone !== null) {
                    $this->syncMilestoneStatus($previousMilestone, $performedBy);
                }
            }

            $this->updateMilestoneOnDeliverableChange($deliverable, $performedBy);
        }

        DeliverableEvent::dispatch(
            $deliverable,
            DeliverableAction::UPDATED,
            $performedBy,
            []
        );

        return $deliverable;
    }

    public function getDeliverablesForProject(string $projectUniqueId, array $filters = []): LengthAwarePaginator
    {
        $query = Deliverable::query()
            ->where('project_unique_id', $projectUniqueId)
            ->with('milestone');
        $query = $this->applyFilters($query, $filters);

        return $this->paginateQuery($query, $filters);
    }

    public function getDeliverablesForMilestone(string $milestoneUniqueId, array $filters = []): LengthAwarePaginator
    {
        $query = Deliverable::query()->where('milestone_unique_id', $milestoneUniqueId);
        $query = $this->applyFilters($query, $filters);

        return $this->paginateQuery($query, $filters);
    }

    public function changeStatus(
        Deliverable $deliverable,
        DeliverableStatus $status,
        User $performedBy,
        ?User $approvedBy = null,
    ): Deliverable {
        $updateData = ['status' => $status];

        if ($status === DeliverableStatus::APPROVED && $approvedBy) {
            $updateData['approved_at'] = now();
            $updateData['approved_by_unique_id'] = $approvedBy->unique_id;
        }

        $deliverable->update($updateData);

        $deliverable = $deliverable->fresh();

        $this->updateMilestoneOnDeliverableChange($deliverable, $performedBy);

        return $deliverable;
    }

    public function approveDeliverable(Deliverable $deliverable, User $approver): Deliverable
    {
        $updated = $this->changeStatus($deliverable, DeliverableStatus::APPROVED, $approver, $approver);

        DeliverableEvent::dispatch(
            $updated,
            DeliverableAction::APPROVED,
            $approver,
            []
        );

        return $updated;
    }

    public function rejectDeliverable(Deliverable $deliverable, User $rejectedBy, ?string $feedback = null): Deliverable
    {
        $updated = $this->changeStatus($deliverable, DeliverableStatus::REJECTED, $rejectedBy);

        DeliverableEvent::dispatch(
            $updated,
            DeliverableAction::REJECTED,
            $rejectedBy,
            [
                'feedback' => $feedback,
            ]
        );

        return $updated;
    }

    private function updateMilestoneOnDeliverableChange(Deliverable $deliverable, User $performedBy): void
    {
        /** @var Milestone|null $milestone */
        $milestone = $deliverable->milestone;

        if ($milestone === null) {
            return;
        }

        $this->syncMilestoneStatus($milestone, $performedBy);
    }

    private function syncMilestoneStatus(Milestone $milestone, User $performedBy): void
    {
        $hasDeliverables = $milestone->deliverables()->exists();

        $allApproved = $hasDeliverables && $milestone->deliverables()
            ->where('status', '!=', DeliverableStatus::APPROVED)
            ->doesntExist();

        if ($allApproved && ! $milestone->is_completed) {
            $milestone->update([
                'status' => MilestoneStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            MilestoneEvent::dispatch(
                $milestone->fresh(),
                MilestoneAction::COMPLETED,
                $performedBy,
                ['auto_completed' => true],
            );
        } elseif (! $allApproved && $milestone->is_completed) {
            $milestone->update([
                'status' => MilestoneStatus::IN_PROGRESS,
                'completed_at' => null,
            ]);

            MilestoneEvent::dispatch(
                $milestone->fresh(),
                MilestoneAction::STATUS_CHANGED,
                $performedBy,
                ['auto_uncompleted' => true],
            );
        }
    }

    private function getNextOrder(string $projectUniqueId, ?string $milestoneUniqueId): int
    {
        $query = Deliverable::where('project_unique_id', $projectUniqueId);

        if ($milestoneUniqueId) {
            $query->where('milestone_unique_id', $milestoneUniqueId);
        }

        return (int) $query->lockForUpdate()->max('order') + 1;
    }
}
