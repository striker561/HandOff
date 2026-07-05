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
use App\Models\DeliverableFile;
use App\Models\Milestone;
use App\Models\User;
use App\Services\Storage\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliverableService extends BaseCRUDService
{
    private StorageService $storage;

    private MilestoneService $milestoneService;

    public function __construct(MilestoneService $milestoneService)
    {
        $this->storage = new StorageService('filesystems.deliverables_disk');
        $this->milestoneService = $milestoneService;
    }

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

    public function uploadFile(
        Deliverable $deliverable,
        UploadedFile $file,
        User $uploadedBy
    ): DeliverableFile {
        return DB::transaction(function () use ($deliverable, $file, $uploadedBy) {
            // Mark previous files as not latest
            DeliverableFile::where('deliverable_unique_id', $deliverable->unique_id)
                ->update(['is_latest' => false]);

            // Generate unique filename
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $directory = "deliverables/{$deliverable->project_unique_id}";

            // Store file using configured disk
            $path = $this->storage->putFileAs($directory, $file, $filename);

            // Get next version
            $nextVersion = $this->getNextFileVersion($deliverable->unique_id);

            // Create file record
            $deliverableFile = DeliverableFile::create([
                'deliverable_unique_id' => $deliverable->unique_id,
                'uploaded_by_unique_id' => $uploadedBy->unique_id,
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'version' => $nextVersion,
                'is_latest' => true,
                'download_count' => 0,
            ]);

            // Update deliverable version
            $deliverable->update(['version' => $nextVersion]);

            DeliverableEvent::dispatch(
                $deliverable,
                DeliverableAction::FILE_UPLOADED,
                $uploadedBy,
                [
                    'file_unique_id' => $deliverableFile->unique_id,
                    'original_filename' => $deliverableFile->original_filename,
                    'version' => $deliverableFile->version,
                ]
            );

            return $deliverableFile;
        });
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

    public function trackDownload(DeliverableFile $file): void
    {
        $file->increment('download_count');
    }

    public function getLatestFile(string $deliverableUniqueId): ?DeliverableFile
    {
        return DeliverableFile::where('deliverable_unique_id', $deliverableUniqueId)
            ->where('is_latest', true)
            ->first();
    }

    public function getFileVersions(string $deliverableUniqueId): array
    {
        return DeliverableFile::where('deliverable_unique_id', $deliverableUniqueId)
            ->orderByDesc('version')
            ->get()
            ->toArray();
    }

    public function downloadFile(DeliverableFile $file, User $downloadedBy): ?StreamedResponse
    {
        if (! $this->storage->exists($file->file_path)) {
            return null;
        }

        $this->trackDownload($file);

        /** @var Deliverable|null $deliverable */
        $deliverable = $file->deliverable;
        if ($deliverable) {
            DeliverableEvent::dispatch(
                $deliverable,
                DeliverableAction::FILE_DOWNLOADED,
                $downloadedBy,
                [
                    'file_unique_id' => $file->unique_id,
                    'download_count' => $file->download_count,
                ]
            );
        }

        return $this->storage->download($file->file_path, $file->original_filename);
    }

    public function deleteFile(DeliverableFile $file, User $deletedBy): bool
    {
        if ($this->storage->exists($file->file_path)) {
            $this->storage->delete($file->file_path);
        }

        $deleted = $file->delete();

        if ($deleted) {
            /** @var Deliverable|null $deliverable */
            $deliverable = $file->deliverable;
            if ($deliverable) {
                DeliverableEvent::dispatch(
                    $deliverable,
                    DeliverableAction::FILE_DELETED,
                    $deletedBy,
                    [
                        'file_unique_id' => $file->unique_id,
                        'original_filename' => $file->original_filename,
                    ]
                );
            }
        }

        return $deleted;
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

    private function getNextFileVersion(string $deliverableUniqueId): int
    {
        return (int) DeliverableFile::where('deliverable_unique_id', $deliverableUniqueId)
            ->lockForUpdate()
            ->max('version') + 1;
    }
}
