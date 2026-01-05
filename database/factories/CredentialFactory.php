<?php

namespace Database\Factories;

use App\Models\Project;
use App\Enums\Credential\CredentialType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Credential>
 */
class CredentialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_unique_id' => Project::factory(),
            'name' => fake()->words(3, true),
            'type' => fake()->randomElement(CredentialType::cases()),
            'username' => fake()->userName(),
            'password' => fake()->password(),
            'url' => fake()->url(),
            'notes' => fake()->optional()->paragraph(),
            'metadata' => [
                'environment' => fake()->randomElement(['production', 'staging', 'development']),
                'last_rotated' => fake()->optional()->dateTime()?->format('Y-m-d'),
            ],
            'last_accessed_at' => fake()->optional(0.6)->dateTime(),
        ];
    }
}
