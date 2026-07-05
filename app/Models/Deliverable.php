<?php

namespace App\Models;

use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Deliverable\DeliverableType;
use App\Models\Concerns\BelongsToProject;
use Database\Factories\DeliverableFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property DeliverableType $type
 * @property DeliverableStatus $status
 * @property Carbon|null $due_date
 * @property-read Project|null $project
 * @property-read Milestone|null $milestone
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static> forProject(string $projectUniqueId)
 */
class Deliverable extends BaseModel
{
    use BelongsToProject;

    /** @use HasFactory<DeliverableFactory> */
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

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'deliverable_unique_id', 'unique_id');
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
     * @return object{total: int, approved: int}
     */
    public static function statusCountsForProject(string $projectUniqueId): object
    {
        $stats = static::query()
            ->forProject($projectUniqueId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved', [DeliverableStatus::APPROVED->value])
            ->first();

        return (object) [
            'total' => (int) ($stats->total ?? 0),
            'approved' => (int) ($stats->approved ?? 0),
        ];
    }

    /**
     * @return EloquentCollection<int, self>
     */
    public static function recentForProject(string $projectUniqueId, int $limit = 5): EloquentCollection
    {
        return Deliverable::query()
            ->forProject($projectUniqueId)
            ->with('milestone:id,unique_id,name')
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }
}
