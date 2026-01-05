<?php

namespace Database\Factories;

use App\Enums\Notification\NotificationType;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{User, Project, Deliverable, Meeting, Milestone, Credential};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_unique_id' => User::factory(),
            'type' => fake()->randomElement(NotificationType::cases()),
            'data' => [
                'title' => fake()->sentence(),
                'message' => fake()->paragraph(),
                'action_url' => fake()->optional(0.7)->url(),
            ],
            'read_at' => fake()->optional(0.5)->dateTime(),
        ];
    }

    public function forNotifiable($notifiable): static
    {
        return $this->for($notifiable, 'notifiable');
    }

    public function forProject($project = null): static
    {
        return $this->forNotifiable(
            $project ?? Project::factory(),
        );
    }

    public function forDeliverable($deliverable = null): static
    {
        return $this->forNotifiable(
            $deliverable ?? Deliverable::factory(),
        );
    }

    public function forMeeting($meeting = null): static
    {
        return $this->forNotifiable(
            $meeting ?? Meeting::factory(),
        );
    }

    public function forMilestone($milestone = null): static
    {
        return $this->forNotifiable(
            $milestone ?? Milestone::factory(),
        );
    }

    public function forCredential($credential = null): static
    {
        return $this->forNotifiable(
            $credential ?? Credential::factory(),
        );
    }

    public function unread(): static
    {
        return $this->state(fn() => [
            'read_at' => null,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn() => [
            'read_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
