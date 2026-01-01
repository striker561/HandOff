<?php

namespace App\Models;

use App\Enums\{DeliverableStatus, DeliverableType};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphMany};

class Deliverable extends BaseModel
{
    protected $fillable = [
        'project_unique_id',
        'milestone_unique_id',
        'created_by_unique_id',
        'name',
        'description',
        'type',
        'status',
        'version',
        'order',
        'due_date',
        'approved_at',
        'approved_by_unique_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => DeliverableType::class,
            'status' => DeliverableStatus::class,
            'order' => 'integer',
            'due_date' => 'date',
            'approved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_unique_id', 'unique_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class, 'milestone_unique_id', 'unique_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_unique_id', 'unique_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_unique_id', 'unique_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DeliverableFile::class, 'deliverable_unique_id', 'unique_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable', 'commentable_type', 'commentable_id', 'unique_id');
    }
}
