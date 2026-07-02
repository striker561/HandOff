<?php

namespace Database\Factories;

use App\Enums\Notification\NotificationType;
use App\Models\Credential;
use App\Models\Deliverable;
use App\Models\Meeting;
use App\Models\Milestone;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    public function definition(): array
    {
        $project = Project::factory()->create();

        return [
            'user_unique_id' => fn () => User::factory()->create()->unique_id,
            'type' => fake()->randomElement(NotificationType::cases()),
            'notifiable_type' => Project::class,
            'notifiable_id' => $project->unique_id,
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
        return $this->state(fn () => [
            'read_at' => null,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn () => [
            'read_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
