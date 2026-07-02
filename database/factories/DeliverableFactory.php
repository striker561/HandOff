<?php

namespace Database\Factories;

use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Deliverable\DeliverableType;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deliverable>
 */
class DeliverableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_unique_id' => fn () => Project::factory()->create()->unique_id,
            'milestone_unique_id' => fn () => Milestone::factory()->create()->unique_id,
            'created_by_unique_id' => fn () => User::factory()->create()->unique_id,
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
        return $this->state(fn () => [
            'status' => DeliverableStatus::APPROVED,
            'approved_at' => now(),
            'approved_by_unique_id' => fn () => User::factory()->create()->unique_id,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => DeliverableStatus::IN_REVIEW,
        ]);
    }
}
