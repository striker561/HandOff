<?php

namespace App\Services;

use App\Enums\Deliverable\DeliverableAction;
use App\Events\Deliverable\DeliverableEvent;
use App\Models\Deliverable;
use App\Models\DeliverableFile;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliverableFileService
{
    /**
     * @param  list<UploadedFile>  $files
     * @return list<DeliverableFile>
     */
    public function uploadFiles(Deliverable $deliverable, array $files, User $uploadedBy): array
    {
        $uploaded = [];

        foreach ($files as $file) {
            $uploaded[] = $this->uploadFile($deliverable, $file, $uploadedBy);
        }

        return $uploaded;
    }

    public function uploadFile(
        Deliverable $deliverable,
        UploadedFile $file,
        User $uploadedBy
    ): DeliverableFile {
        return DB::transaction(function () use ($deliverable, $file, $uploadedBy) {
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $directory = "deliverables/{$deliverable->project_unique_id}";

            $path = $this->disk()->putFileAs($directory, $file, $filename);

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

    /**
     * @return Collection<int, DeliverableFile>
     */
    public function getActiveFiles(string $deliverableUniqueId): Collection
    {
        return DeliverableFile::query()
            ->where('deliverable_unique_id', $deliverableUniqueId)
            ->where('is_latest', true)
            ->orderBy('created_at')
            ->get();
    }

    public function findFileForDeliverableInProject(
        string $fileUniqueId,
        string $deliverableUniqueId,
        string $projectUniqueId,
    ): ?DeliverableFile {
        return DeliverableFile::query()
            ->where('unique_id', $fileUniqueId)
            ->whereHas('deliverable', fn ($query) => $query
                ->where('unique_id', $deliverableUniqueId)
                ->where('project_unique_id', $projectUniqueId))
            ->first();
    }

    public function getLatestFile(string $deliverableUniqueId): ?DeliverableFile
    {
        return $this->getActiveFiles($deliverableUniqueId)->last();
    }

    public function getFileVersions(string $deliverableUniqueId): array
    {
        return DeliverableFile::where('deliverable_unique_id', $deliverableUniqueId)
            ->orderByDesc('version')
            ->get()
            ->toArray();
    }

    public function streamFile(DeliverableFile $file, User $downloadedBy, bool $inline = true): ?StreamedResponse
    {
        if (! $this->disk()->exists($file->file_path)) {
            return null;
        }

        $this->recordFileAccess($file, $downloadedBy);

        return $this->disk()->response(
            $file->file_path,
            $file->original_filename,
            [
                'Content-Type' => $file->mimeTypeValue(),
                'Content-Disposition' => sprintf(
                    '%s; filename="%s"',
                    $inline ? 'inline' : 'attachment',
                    addcslashes($file->original_filename, '"\\'),
                ),
            ],
        );
    }

    public function downloadFile(DeliverableFile $file, User $downloadedBy): ?StreamedResponse
    {
        return $this->streamFile($file, $downloadedBy, inline: false);
    }

    private function recordFileAccess(DeliverableFile $file, User $downloadedBy): void
    {
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
    }

    public function deleteFile(DeliverableFile $file, User $deletedBy): bool
    {
        if ($this->disk()->exists($file->file_path)) {
            $this->disk()->delete($file->file_path);
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

    private function disk(): Filesystem
    {
        return Storage::disk('deliverables');
    }

    private function getNextFileVersion(string $deliverableUniqueId): int
    {
        return (int) DeliverableFile::where('deliverable_unique_id', $deliverableUniqueId)
            ->lockForUpdate()
            ->max('version') + 1;
    }
}
