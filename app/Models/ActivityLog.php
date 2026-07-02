<?php

namespace App\Models;

use App\Enums\ActivityLog\LogName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};

class ActivityLog extends BaseModel
{
    /** @use HasFactory<\Database\Factories\ActivityLogFactory> */
    public $timestamps = true;

    /**
     * ActivityLog table doesn't have a unique_id column.
     */
    public function uniqueIds(): array
    {
        return [];
    }

    protected $fillable = [
        'user_unique_id',
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'log_name' => LogName::class,
            'properties' => 'array',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id', 'unique_id');
    }

    public function causer(): MorphTo
    {
        return $this->morphTo('causer', 'causer_type', 'causer_id', 'unique_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_unique_id', 'unique_id');
    }
}
