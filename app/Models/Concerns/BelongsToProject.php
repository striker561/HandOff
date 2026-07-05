<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder<static> forProject(string $projectUniqueId)
 */
trait BelongsToProject
{
    #[Scope]
    protected function forProject(Builder $query, string $projectUniqueId): void
    {
        $query->where($this->qualifyColumn('project_unique_id'), $projectUniqueId);
    }
}
