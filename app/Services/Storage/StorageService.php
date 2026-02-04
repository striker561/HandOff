<?php

namespace App\Services\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StorageService
{
    private FilesystemAdapter $disk;

    public function __construct(string $configKey)
    {
        $diskName = config($configKey);
        $this->disk = Storage::disk($diskName);
    }

    public function putFileAs(string $directory, UploadedFile $file, string $filename): string
    {
        return $this->disk->putFileAs($directory, $file, $filename);
    }


    public function download(string $path, string $downloadName): StreamedResponse
    {
        return $this->disk->download($path, $downloadName);
    }


    public function stream(string $path): StreamedResponse
    {
        return $this->disk->response($path);
    }


    public function exists(string $path): bool
    {
        return $this->disk->exists($path);
    }


    public function delete(string $path): bool
    {
        return $this->disk->delete($path);
    }

    public function temporaryUrl(string $path, int $minutesValid = 30): ?string
    {
        try {
            return $this->disk->temporaryUrl($path, now()->addMinutes($minutesValid));
        } catch (\RuntimeException $e) {
            // Driver doesn't support temporaryUrl
            return null;
        }
    }

    public function size(string $path): int
    {
        return $this->disk->size($path);
    }

    public function lastModified(string $path): int
    {
        return $this->disk->lastModified($path);
    }

    public function getDisk(): FilesystemAdapter
    {
        return $this->disk;
    }

    // for front end upload and steaming
    public function createPresignedUploadUrl()
    {

    }

}
