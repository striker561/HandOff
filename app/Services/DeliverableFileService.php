<?php

namespace App\Services;

use App\Enums\Deliverable\DeliverableAction;
use App\Events\Deliverable\DeliverableEvent;
use App\Models\Deliverable;
use App\Models\DeliverableFile;
use App\Models\User;
use App\Services\Storage\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliverableFileService
{
    private StorageService $storage;

    public function __construct()
    {
        $this->storage = new StorageService('filesystems.deliverables_disk');
    }

    public function uploadFile(
        Deliverable $deliverable,
        UploadedFile $file,
        User $uploadedBy
    ): DeliverableFile {
        return DB::transaction(function () use ($deliverable, $file, $uploadedBy) {
            DeliverableFile::where('deliverable_unique_id', $deliverable->unique_id)
                ->update(['is_latest' => false]);

            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $directory = "deliverables/{$deliverable->project_unique_id}";

            $path = $this->storage->putFileAs($directory, $file, $filename);

            $nextVersion = $this->getNextFileVersion($deliverable->unique_id);

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

    private function getNextFileVersion(string $deliverableUniqueId): int
    {
        return (int) DeliverableFile::where('deliverable_unique_id', $deliverableUniqueId)
            ->lockForUpdate()
            ->max('version') + 1;
    }
}
