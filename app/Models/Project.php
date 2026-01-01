<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphMany};

class Project extends BaseModel
{

    protected $fillable = [
        'client_unique_id',
        'name',
        'slug',
        'description',
        'status',
        'start_date',
        'due_date',
        'completed_at',
        'budget',
        'currency',
        'progress_percentage',
        'color',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'budget' => 'decimal:2',
            'progress_percentage' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_unique_id', 'unique_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class, 'project_unique_id', 'unique_id');
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class, 'project_unique_id', 'unique_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable', 'commentable_type', 'commentable_id', 'unique_id');
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'project_unique_id', 'unique_id');
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(Credential::class, 'project_unique_id', 'unique_id');
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
