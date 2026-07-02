<?php

namespace App\Models;

use App\Enums\Notification\NotificationType;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends BaseModel
{
    /** @use HasFactory<NotificationFactory> */
    public $timestamps = true;

    protected $fillable = [
        'user_unique_id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo('notifiable', 'notifiable_type', 'notifiable_id', 'unique_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_unique_id', 'unique_id');
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
