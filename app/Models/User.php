<?php

namespace App\Models;


use App\Enums\User\AccountRole;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\{HasMany, MorphMany};

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => AccountRole::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //Auto gen the uuid for the unique id col
    public function uniqueIds(): array
    {
        return ['unique_id'];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_unique_id', 'unique_id');
    }

    public function createdDeliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class, 'created_by_unique_id', 'unique_id');
    }

    public function approvedDeliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class, 'approved_by_unique_id', 'unique_id');
    }

    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(DeliverableFile::class, 'uploaded_by_unique_id', 'unique_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'user_unique_id', 'unique_id');
    }

    public function scheduledMeetings(): HasMany
    {
        return $this->hasMany(Meeting::class, 'scheduled_by_unique_id', 'unique_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_unique_id', 'unique_id');
    }

    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_unique_id', 'unique_id')
            ->whereNull('read_at');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_unique_id', 'unique_id');
    }

    public function causedActivities(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'causer', 'causer_type', 'causer_id', 'unique_id');
    }
}
