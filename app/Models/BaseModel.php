<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $unique_id
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
