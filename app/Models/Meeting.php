<?php

namespace App\Models;

use App\Enums\MeetingStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends BaseModel
{
    protected $fillable = [
        'project_unique_id',
        'deliverable_unique_id',
        'scheduled_by_unique_id',
        'title',
        'description',
        'scheduled_at',
        'duration_minutes',
        'location',
        'status',
        'meeting_notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => MeetingStatus::class,
            'scheduled_at' => 'datetime',
            'duration_minutes' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_unique_id', 'unique_id');
    }

    public function deliverable(): BelongsTo
    {
        return $this->belongsTo(Deliverable::class, 'deliverable_unique_id', 'unique_id');
    }

    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by_unique_id', 'unique_id');
    }
}
