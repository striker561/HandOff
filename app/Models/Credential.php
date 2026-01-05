<?php

namespace App\Models;

use App\Enums\CredentialType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphMany};

class Credential extends BaseModel
{
    /** @use HasFactory<\Database\Factories\CredentialFactory> */
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
        'password',
    ];

    protected function casts(): array
    {
        return [
            'type' => CredentialType::class,
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
}
