<?php

namespace App\Models;

use App\Enums\Milestone\MilestoneStatus;
use App\Models\Concerns\BelongsToProject;
use Database\Factories\MilestoneFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * @property-read Project|null $project
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static> forProject(string $projectUniqueId)
 */
class Milestone extends BaseModel
{
    use BelongsToProject;

    /** @use HasFactory<MilestoneFactory> */
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

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === MilestoneStatus::COMPLETED;
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

    /**
     * @return Collection<int, self>
     */
    public static function pipelineForProject(string $projectUniqueId): Collection
    {
        return static::query()
            ->forProject($projectUniqueId)
            ->withCount('deliverables')
            ->orderBy('order')
            ->get();
    }
}
