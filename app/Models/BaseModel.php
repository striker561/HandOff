<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $unique_id
 *
 * ## Why UUIDs instead of auto-increment IDs?
 *
 * Every model uses a UUID (`unique_id`) as its route key and relationship foreign key.
 * The auto-increment `id` column exists on every table but is never exposed in URLs,
 * APIs, or relationships.
 *
 * **Reasoning:**
 * - Auto-increment IDs leak row counts (e.g. `/projects/42` reveals there are at least 42 projects).
 * - UUIDs prevent enumeration attacks — an attacker cannot guess or iterate over resource URLs.
 * - This is important for multi-tenant agency software where competitor intelligence matters.
 *
 * **Trade-off considered:** ULIDs (sortable, shorter at 26 chars) were evaluated but UUID v4 won
 * for ecosystem familiarity. Laravel supports both natively (HasUlids / HasUuids). The migration
 * cost to switch is negligible while the app is pre-release, but UUID index fragmentation is
 * irrelevant at project-management scale.
 *
 * **Key gotcha:** `$model::find($id)` will NOT work with the auto-increment `id`. Always use
 * `where('unique_id', $value)` or `Model::find($model->getRouteKeyName())` when working with
 * raw IDs. Route model binding works automatically via `getRouteKeyName()`.
 */
class BaseModel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Auto generate UUID for the unique_id column
     */
    public function uniqueIds(): array
    {
        return ['unique_id'];
    }
}
