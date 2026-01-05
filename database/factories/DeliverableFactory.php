<?php

namespace Database\Factories;

use App\Models\{Project, Milestone, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Deliverable\{DeliverableStatus, DeliverableType};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deliverable>
 */
class DeliverableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_unique_id' => Project::factory(),
            'milestone_unique_id' => Milestone::factory(),
            'created_by_unique_id' => User::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(DeliverableType::cases()),
            'status' => fake()->randomElement(DeliverableStatus::cases()),
            'version' => '1.0',
            'order' => 0,
            'due_date' => now()->addDays(rand(7, 30)),
            'approved_at' => null,
            'approved_by_unique_id' => null,
            'metadata' => [
                'tags' => fake()->words(3),
                'category' => fake()->word(),
            ],
        ];
    }

    public function approved(): static
    {
        return $this->state(fn() => [
            'status' => DeliverableStatus::APPROVED,
            'approved_at' => now(),
            'approved_by_unique_id' => User::factory(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn() => [
            'status' => DeliverableStatus::IN_REVIEW,
        ]);
    }
}
