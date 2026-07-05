<?php

/*
|---------------------------------------------------------------------------
| Livewire Configuration
|---------------------------------------------------------------------------
|
| Only values that differ from Livewire defaults are listed here.
| See: https://livewire.laravel.com/docs/config
|
*/

return [

    /*
    | When AWS_BUCKET is set, the deliverables disk uses S3 and Livewire temp
    | uploads use the same disk. See CONTRIBUTING.md § File storage.
    */

    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK') ?: (static function (): ?string {
            return config('filesystems.disks.deliverables.driver') === 's3' ? 'deliverables' : null;
        })(),
        'rules' => ['file', 'max:'.(int) env('UPLOAD_MAX_KB', 102400)],
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png',
            'gif',
            'bmp',
            'svg',
            'wav',
            'mp4',
            'mov',
            'avi',
            'wmv',
            'mp3',
            'm4a',
            'jpg',
            'jpeg',
            'mpga',
            'webp',
            'wma',
        ],
        'max_upload_time' => 30,
        'cleanup' => true,
    ],

];
