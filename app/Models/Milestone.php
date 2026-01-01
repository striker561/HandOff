<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphMany};

class Milestone extends BaseModel
{
    protected $fillable = [
        'project_unique_id',
        'name',
        'description',
        'order',
        'status',
        'start_date',
        'due_date',
        'completed_at',
        'progress_percentage',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'progress_percentage' => 'integer',
            'status' => MilestoneStatus::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_unique_id', 'unique_id');
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class, 'milestone_unique_id', 'unique_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable', 'commentable_type', 'commentable_id', 'unique_id');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable', 'notifiable_type', 'notifiable_id', 'unique_id');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject', 'subject_type', 'subject_id', 'unique_id');
    }
}
