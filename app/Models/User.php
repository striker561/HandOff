<?php

namespace App\Models;


use App\Enums\AccountRole;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
    ];

    protected function casts(): array
    {
        return [
            'role'=> AccountRole::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //Auto gen the uuid for the unique id col
    public function uniqueIds(): array
    {
        return ['unique_id'];
    }
}
