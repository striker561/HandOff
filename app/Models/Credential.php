<?php

namespace App\Models;

use App\Enums\Credential\CredentialType;
use App\Models\Concerns\BelongsToProject;
use Database\Factories\CredentialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property CredentialType $type
 * @property Carbon|null $last_accessed_at
 * @property-read Project|null $project
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static> forProject(string $projectUniqueId)
 */
class Credential extends BaseModel
{
    use BelongsToProject;

    /** @use HasFactory<CredentialFactory> */
    protected $fillable = [
        'project_unique_id',
        'name',
        'type',
        'username',
        'password',
        'url',
        'notes',
        'metadata',
        'last_accessed_at',
    ];

    protected $hidden = [
        'username',
        'password',
        'url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => CredentialType::class,
            'username' => 'encrypted',
            'password' => 'encrypted',
            'url' => 'encrypted',
            'notes' => 'encrypted',
            'last_accessed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_unique_id', 'unique_id');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable', 'notifiable_type', 'notifiable_id', 'unique_id');
    }

    public static function countForProject(string $projectUniqueId): int
    {
        return static::query()
            ->forProject($projectUniqueId)
            ->count();
    }
}
