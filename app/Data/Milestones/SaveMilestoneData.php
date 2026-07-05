<?php

namespace App\Data\Milestones;

use App\Enums\Milestone\MilestoneStatus;

readonly class SaveMilestoneData
{
    public function __construct(
        public string $projectUniqueId,
        public string $name,
        public ?string $description,
        public ?string $dueDate,
        public ?MilestoneStatus $status = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            projectUniqueId: $data['project_unique_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            dueDate: $data['due_date'] ?? null,
            status: isset($data['status']) ? MilestoneStatus::tryFrom($data['status']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toCreateAttributes(int $order): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'project_unique_id' => $this->projectUniqueId,
            'due_date' => $this->dueDate,
            'order' => $order,
            'status' => ($this->status ?? MilestoneStatus::PENDING)->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toUpdateAttributes(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'due_date' => $this->dueDate,
        ];
    }
}
