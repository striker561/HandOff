<?php

namespace App\Models;

use App\Enums\DeliverableStatus;
use App\Enums\DeliverableType;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Deliverable extends BaseModel
{
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

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'unique_id', 'project_unique_id');
    }

    public function milestone(): HasOne
    {
        return $this->hasOne(Milestone::class, 'unique_id', 'milestone_unique_id');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'unique_id', 'created_by_unique_id');
    }

    public function approved_by(): HasOne
    {
        return $this->hasOne(User::class, 'unique_id', 'approved_by_unique_id');
    }
}
