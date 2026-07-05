<?php

namespace App\Services;

use App\Data\Deliverables\SaveDeliverableData;
use App\Enums\Deliverable\DeliverableAction;
use App\Enums\Deliverable\DeliverableStatus;
use App\Events\Deliverable\DeliverableEvent;
use App\Models\Deliverable;
use App\Models\DeliverableFile;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class DeliverableService extends BaseCRUDService
{
    public function __construct(
        private MilestoneService $milestoneService,
        private DeliverableFileService $deliverableFileService,
    ) {}

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

    public function findDeliverableForProject(string $uniqueId, string $projectUniqueId): ?Deliverable
    {
        return Deliverable::query()
            ->where('unique_id', $uniqueId)
            ->where('project_unique_id', $projectUniqueId)
            ->first();
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

        $this->syncMilestoneForDeliverable($deliverable, $performedBy);

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
                $previousMilestone = $this->milestoneService->findMilestoneForProject(
                    $previousMilestoneUniqueId,
                    $deliverable->project_unique_id,
                );

                if ($previousMilestone !== null) {
                    $this->milestoneService->syncFromDeliverables($previousMilestone, $performedBy);
                }
            }

            $this->syncMilestoneForDeliverable($deliverable, $performedBy);
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

        $this->syncMilestoneForDeliverable($deliverable, $performedBy);

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

    public function submitForReview(Deliverable $deliverable, User $performedBy): Deliverable
    {
        $fromStatus = $deliverable->status;

        $updated = $this->changeStatus($deliverable, DeliverableStatus::IN_REVIEW, $performedBy);

        DeliverableEvent::dispatch(
            $updated,
            DeliverableAction::STATUS_CHANGED,
            $performedBy,
            [
                'from_status' => $fromStatus->value,
                'to_status' => DeliverableStatus::IN_REVIEW->value,
            ]
        );

        return $updated;
    }

    public function deleteDeliverable(Deliverable $deliverable, User $performedBy): bool
    {
        /** @var DeliverableFile $file */
        foreach ($deliverable->files as $file) {
            $this->deliverableFileService->deleteFile($file, $performedBy);
        }

        $deleted = (bool) $deliverable->delete();

        if ($deleted) {
            DeliverableEvent::dispatch(
                $deliverable,
                DeliverableAction::DELETED,
                $performedBy,
                []
            );

            $this->syncMilestoneForDeliverable($deliverable, $performedBy);
        }

        return $deleted;
    }

    private function syncMilestoneForDeliverable(Deliverable $deliverable, User $performedBy): void
    {
        $milestone = $deliverable->milestone;

        if ($milestone === null) {
            return;
        }

        $this->milestoneService->syncFromDeliverables($milestone, $performedBy);
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
