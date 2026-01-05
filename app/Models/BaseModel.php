<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BaseModel extends Model
{
    use HasUuids, SoftDeletes;

    /**
     * Auto generate UUID for the unique_id column
     */
    public function uniqueIds(): array
    {
        return ['unique_id'];
    }
}
