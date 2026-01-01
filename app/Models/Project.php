<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends BaseModel
{

    protected $fillable = [
        'client_unique_id',
        'name',
        'slug',
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


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'budget' => 'decimal:2',
            'progress_percentage' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'unique_id', 'client_unique_id');
    }
}
