<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function deliverable(): BelongsTo
    {
        return $this->belongsTo(Deliverable::class, 'deliverable_unique_id', 'unique_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_unique_id', 'unique_id');
    }
}
