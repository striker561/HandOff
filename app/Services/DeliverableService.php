<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Services\Storage\StorageService;
use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Deliverable\DeliverableAction;
use App\Events\Deliverable\DeliverableEvent;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\{Deliverable, DeliverableFile, User};
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliverableService extends BaseCRUDService
{
    private StorageService $storage;

    public function __construct()
    {
        $this->storage = new StorageService('filesystems.deliverables_disk');
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


    public function createDeliverable(array $data, User $performedBy): Deliverable
    {
        $nextOrder = $this->getNextOrder(
            $data['project_unique_id'],
            $data['milestone_unique_id'] ?? null
        );

        /** @var Deliverable $deliverable */
        $deliverable = $this->create([
            'project_unique_id' => $data['project_unique_id'],
            'milestone_unique_id' => $data['milestone_unique_id'] ?? null,
            'created_by_unique_id' => $data['created_by_unique_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'status' => DeliverableStatus::DRAFT,
            'version' => 1,
            'order' => $nextOrder,
            'due_date' => $data['due_date'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ]);

        DeliverableEvent::dispatch(
            $deliverable,
            DeliverableAction::CREATED,
            $performedBy,
            []
        );

        return $deliverable;
    }

    public function getDeliverablesForProject(string $projectUniqueId, array $filters = []): LengthAwarePaginator
    {
        $query = Deliverable::query()->where('project_unique_id', $projectUniqueId);
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
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
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
        ?User $approvedBy = null
    ): Deliverable {
        $updateData = ['status' => $status];

        if ($status === DeliverableStatus::APPROVED && $approvedBy) {
            $updateData['approved_at'] = now();
            $updateData['approved_by_unique_id'] = $approvedBy->unique_id;
        }

        $deliverable->update($updateData);

        return $deliverable->fresh();
    }

    public function approveDeliverable(Deliverable $deliverable, User $approver): Deliverable
    {
        $updated = $this->changeStatus($deliverable, DeliverableStatus::APPROVED, $approver);

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
        $updated = $this->changeStatus($deliverable, DeliverableStatus::REJECTED);

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
        if (!$this->storage->exists($file->file_path)) {
            return null;
        }

        $this->trackDownload($file);

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
