<?php

namespace App\Models;

use App\Enums\DeliverableFile\MimeType;
use Database\Factories\DeliverableFileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Deliverable|null $deliverable
 * @property-read User|null $uploadedBy
 */
class DeliverableFile extends BaseModel
{
    /** @use HasFactory<DeliverableFileFactory> */
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
            'mime_type' => MimeType::class,
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
