<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\Meeting\{MeetingLocation, MeetingStatus};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphMany};

class Meeting extends BaseModel
{
    /** @use HasFactory<\Database\Factories\MeetingFactory> */
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
            'location' => MeetingLocation::class,
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


    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable', 'notifiable_type', 'notifiable_id', 'unique_id');
    }
}
