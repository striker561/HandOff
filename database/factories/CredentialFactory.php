<?php

namespace Database\Factories;

use App\Enums\Credential\CredentialType;
use App\Models\Credential;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Credential>
 */
class CredentialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_unique_id' => fn () => Project::factory()->create()->unique_id,
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
