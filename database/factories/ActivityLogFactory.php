<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{User, Project, Milestone, Deliverable, Meeting};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_unique_id' => User::factory(),
            'log_name' => fake()->randomElement(['default', 'auth', 'project', 'deliverable', 'meeting']),
            'description' => fake()->sentence(),
            'properties' => [
                'action' => fake()->randomElement(['created', 'updated', 'deleted', 'viewed']),
                'timestamp' => now()->toIso8601String(),
            ],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    public function forSubject($subject): static
    {
        return $this->for($subject, 'subject');
    }


    public function forProject($project = null): static
    {
        return $this->forSubject(
            $project ?? Project::factory()
        );
    }

    public function forDeliverable($deliverable = null): static
    {
        return $this->forSubject(
            $deliverable ?? Deliverable::factory(),
        );
    }

    public function forMilestone($milestone = null): static
    {
        return $this->forSubject(
            $milestone ?? Milestone::factory(),
        );
    }

    public function forMetting($milestone = null): static
    {
        return $this->forSubject(
            $milestone ?? Meeting::factory(),
        );
    }

    public function causedBy($causer): static
    {
        return $this->for($causer, 'causer');
    }

    public function created(): static
    {
        return $this->state(fn() => [
            'log_name' => 'default',
            'description' => 'Created new resource',
            'properties' => [
                'action' => 'created',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function updated(): static
    {
        return $this->state(fn() => [
            'log_name' => 'default',
            'description' => 'Updated resource',
            'properties' => [
                'action' => 'updated',
                'timestamp' => now()->toIso8601String(),
                'old' => ['status' => 'pending'],
                'new' => ['status' => 'active'],
            ],
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn() => [
            'log_name' => 'default',
            'description' => 'Deleted resource',
            'properties' => [
                'action' => 'deleted',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
