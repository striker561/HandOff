<?php

namespace Database\Factories;

use App\Enums\Meeting\MeetingStatus;
use App\Models\{Project, Deliverable, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Meeting>
 */
class MeetingFactory extends Factory
{
    public function definition(): array
    {
        $scheduledAt = now()->addDays(rand(1, 30))->setHour(rand(9, 17))->setMinute(0);

        return [
            'project_unique_id' => Project::factory(),
            'deliverable_unique_id' => null,
            'scheduled_by_unique_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => fake()->randomElement([30, 60, 90, 120]),
            'location' => fake()->randomElement([
                fake()->url(),
                'Conference Room A',
                'Zoom',
                'Google Meet',
                'Microsoft Teams',
            ]),
            'status' => fake()->randomElement(MeetingStatus::cases()),
            'meeting_notes' => fake()->optional(0.4)->paragraph(),
            'metadata' => [
                'attendees_count' => fake()->numberBetween(2, 10),
                'recording_url' => fake()->optional(0.3)->url(),
            ],
        ];
    }

    public function withDeliverable(): static
    {
        return $this->state(fn() => [
            'deliverable_unique_id' => Deliverable::factory(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn() => [
            'status' => MeetingStatus::COMPLETED,
            'scheduled_at' => now()->subDays(rand(1, 10)),
            'meeting_notes' => fake()->paragraphs(3, true),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn() => [
            'status' => MeetingStatus::SCHEDULED,
            'scheduled_at' => now()->addDays(rand(1, 14)),
        ]);
    }
}
