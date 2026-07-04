<?php

namespace App\Models;

use App\Enums\Meeting\MeetingLocation;
use App\Enums\Meeting\MeetingStatus;
use App\Models\Concerns\BelongsToProject;
use Carbon\CarbonInterface;
use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * @property-read Project|null $project
 * @property-read Deliverable|null $deliverable
 *
 * @method static Builder<static> upcoming(?CarbonInterface $from = null)
 * @method static Builder<static> orderedBySchedule()
 * @method static Builder<static> forProject(string $projectUniqueId)
 */
class Meeting extends BaseModel
{
    use BelongsToProject;

    /** @use HasFactory<MeetingFactory> */
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

    #[Scope]
    protected function upcoming(Builder $query, ?CarbonInterface $from = null): void
    {
        $from ??= now();

        $query
            ->where('status', MeetingStatus::SCHEDULED)
            ->where('scheduled_at', '>=', $from);
    }

    #[Scope]
    protected function orderedBySchedule(Builder $query): void
    {
        $query->orderBy('scheduled_at');
    }

    public static function nextUpcomingForProject(string $projectUniqueId, ?CarbonInterface $from = null): ?self
    {
        $from ??= now();

        return static::query()
            ->forProject($projectUniqueId)
            ->where('status', MeetingStatus::SCHEDULED)
            ->where('scheduled_at', '>=', $from)
            ->orderBy('scheduled_at', 'asc')
            ->first();
    }

    // ponytail: for reminder emails / digests — add caller when that job exists
    public static function upcomingForNotification(?CarbonInterface $from = null, ?CarbonInterface $until = null): Collection
    {
        $from ??= now();

        $query = static::query()
            ->where('status', MeetingStatus::SCHEDULED)
            ->where('scheduled_at', '>=', $from);

        if ($until !== null) {
            $query->where('scheduled_at', '<=', $until);
        }

        return $query
            ->orderBy('scheduled_at', 'asc')
            ->with(['project.client'])
            ->get();
    }

    /** @return object{total: int, upcoming: int} */
    public static function overviewStatsForProject(string $projectUniqueId, ?CarbonInterface $from = null): object
    {
        $from ??= now();

        $stats = static::query()
            ->forProject($projectUniqueId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? AND scheduled_at >= ? THEN 1 ELSE 0 END) as upcoming', [
                MeetingStatus::SCHEDULED->value,
                $from,
            ])
            ->first();

        return (object) [
            'total' => (int) ($stats->total ?? 0),
            'upcoming' => (int) ($stats->upcoming ?? 0),
        ];
    }
}
