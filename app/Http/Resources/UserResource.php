<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uniqueId' => $this->unique_id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'createdAt' => optional($this->created_at)->toISOString(),
        ];
    }
}
