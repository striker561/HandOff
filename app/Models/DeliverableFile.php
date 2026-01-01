<?php

namespace App\Models;

class DeliverableFile extends BaseModel
{
    protected $fillable = [
        'deliverable_unique_id',
        'uploaded_by_unique_id',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'version',
        'is_latest',
        'download_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_latest' => 'boolean',
            'download_count' => 'integer',
            'metadata' => 'array',
        ];
    }
}
