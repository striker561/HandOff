<?php

namespace Database\Factories;

use App\Enums\Milestone\MilestoneStatus;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Milestone>
 */
class MilestoneFactory extends Factory
{
    public function definition(): array
    {

        $startDate = now()->subDays(rand(0, 30));

        return [
            'project_unique_id' => fn () => Project::factory()->create()->unique_id,
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'order' => 0,
            'status' => fake()->randomElement(MilestoneStatus::cases()),
            'start_date' => $startDate,
            'due_date' => (clone $startDate)->addDays(rand(7, 30)),
            'completed_at' => null,
            'progress_percentage' => rand(0, 100),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => MilestoneStatus::COMPLETED,
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);
    }
}
