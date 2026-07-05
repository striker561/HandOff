<?php

namespace App\Data\Deliverables;

use App\Enums\Deliverable\DeliverableType;

readonly class SaveDeliverableData
{
    public function __construct(
        public string $projectUniqueId,
        public string $milestoneUniqueId,
        public string $name,
        public ?string $description,
        public DeliverableType $type,
        public ?string $dueDate,
        public ?string $createdByUniqueId = null,
        public array $metadata = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            projectUniqueId: $data['project_unique_id'],
            milestoneUniqueId: $data['milestone_unique_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            type: DeliverableType::from($data['type']),
            dueDate: $data['due_date'] ?? null,
            createdByUniqueId: $data['created_by_unique_id'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toCreateAttributes(int $order): array
    {
        return [
            'project_unique_id' => $this->projectUniqueId,
            'milestone_unique_id' => $this->milestoneUniqueId,
            'created_by_unique_id' => $this->createdByUniqueId,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'order' => $order,
            'due_date' => $this->dueDate,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toUpdateAttributes(): array
    {
        return [
            'milestone_unique_id' => $this->milestoneUniqueId,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'due_date' => $this->dueDate,
            'metadata' => $this->metadata,
        ];
    }
}
