<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Project\{ProjectCurrency, ProjectStatus};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{

    public function definition(): array
    {
        $startDate = now()->subDays(rand(0, 30));

        return [
            'client_unique_id' => User::factory(),
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
        return $this->state(fn() => [
            'status' => ProjectStatus::COMPLETED,
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);
    }
}
