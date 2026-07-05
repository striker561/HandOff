<?php

namespace App\Models;

use App\Enums\Project\ProjectCurrency;
use App\Enums\Project\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property ProjectCurrency $currency
 * @property ProjectStatus $status
 * @property Carbon|null $start_date
 * @property Carbon|null $due_date
 * @property string|null $budget
 * @property-read User|null $client
 * @property-read string|null $formatted_budget
 * @property-read string|null $formatted_due_date
 * @property-read string $client_display_name
 * @property-read string $list_summary
 */
class Project extends BaseModel
{
    /** @use HasFactory<ProjectFactory> */
    protected $fillable = [
        'client_unique_id',
        'name',
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
            'currency' => ProjectCurrency::class,
            'status' => ProjectStatus::class,
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'budget' => 'decimal:2',
            'progress_percentage' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'unique_id';
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

    protected function formattedBudget(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->budget === null) {
                return null;
            }

            return $this->currency->symbol().number_format((float) $this->budget, 2);
        });
    }

    protected function formattedDueDate(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->due_date?->format('M j, Y'));
    }

    protected function clientDisplayName(): Attribute
    {
        return Attribute::get(function (): string {
            $client = $this->client;

            return $client !== null ? $client->name : __('Unknown');
        });
    }

    protected function listSummary(): Attribute
    {
        return Attribute::get(fn (): string => collect([
            $this->client_display_name,
            $this->formatted_budget,
        ])->filter()->implode(' · '));
    }
}
