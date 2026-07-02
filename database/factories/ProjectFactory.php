<?php

namespace Database\Factories;

use App\Enums\Project\ProjectCurrency;
use App\Enums\Project\ProjectStatus;
use App\Enums\User\AccountRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $startDate = now()->subDays(rand(0, 30));

        return [
            'client_unique_id' => fn () => User::factory()->create(['role' => AccountRole::CLIENT])->unique_id,
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::cases()),
            'start_date' => $startDate,
            'due_date' => (clone $startDate)->addDays(rand(7, 90)),
            'completed_at' => null,
            'budget' => fake()->randomFloat(2, 500, 50000),
            'currency' => fake()->randomElement(ProjectCurrency::cases()),
            'progress_percentage' => fake()->numberBetween(0, 100),
            'color' => fake()->safeHexColor(),
            'metadata' => [
                'priority' => fake()->randomElement(['low', 'medium', 'high']),
                'source' => 'factory',
            ],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => ProjectStatus::COMPLETED,
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);
    }
}
