<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Milestone extends BaseModel
{
    protected $fillable = [
        'project_unique_id',
        'name',
        'description',
        'order',
        'status',
        'start_date',
        'due_date',
        'completed_at',
        'progress_percentage',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'progress_percentage' => 'integer',
            'status' => MilestoneStatus::class,
        ];
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'unique_id', 'project_unique_id');
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class, 'milestone_unique_id', 'unique_id');
    }
}
