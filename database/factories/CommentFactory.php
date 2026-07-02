<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parent_unique_id' => null,
            'user_unique_id' => fn () => User::factory()->create()->unique_id,
            'body' => fake()->paragraph(),
            'is_internal' => fake()->boolean(30),
            'mentioned_users' => [],
            'read_at' => fake()->optional(0.7)->dateTime(),
        ];
    }

    public function forProject(?Project $project = null): static
    {
        return $this->for(
            $project ?? Project::factory(),
            'commentable'
        );
    }

    public function forMilestone(?Milestone $milestone = null): static
    {
        return $this->for(
            $milestone ?? Milestone::factory(),
            'commentable'
        );
    }

    public function forDeliverable(?Deliverable $deliverable = null): static
    {
        return $this->for(
            $deliverable ?? Deliverable::factory(),
            'commentable'
        );
    }

    public function internal(): static
    {
        return $this->state(fn () => [
            'is_internal' => true,
        ]);
    }

    public function external(): static
    {
        return $this->state(fn () => [
            'is_internal' => false,
        ]);
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

    public function replyTo($comment): static
    {
        return $this->state(fn () => [
            'parent_unique_id' => is_object($comment) ? $comment->unique_id : $comment,
        ]);
    }
}
