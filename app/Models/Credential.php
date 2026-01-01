<?php

namespace App\Models;

use App\Enums\CredentialType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Credential extends BaseModel
{
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
}
