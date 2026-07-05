<?php

namespace App\Data\Projects;

use App\Enums\Project\ProjectCurrency;
use App\Enums\Project\ProjectStatus;

readonly class SaveProjectData
{
    public function __construct(
        public string $clientUniqueId,
        public string $name,
        public ?string $description,
        public ?string $budget,
        public ProjectCurrency $currency,
        public ?string $startDate,
        public ?string $dueDate,
        public ?string $color,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clientUniqueId: $data['client_unique_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            budget: filled($data['budget'] ?? null) ? (string) $data['budget'] : null,
            currency: ProjectCurrency::from($data['currency']),
            startDate: $data['start_date'] ?? null,
            dueDate: $data['due_date'] ?? null,
            color: $data['color'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toCreateAttributes(): array
    {
        return array_merge($this->toUpdateAttributes(), [
            'status' => ProjectStatus::PLANNING,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toUpdateAttributes(): array
    {
        return [
            'client_unique_id' => $this->clientUniqueId,
            'name' => $this->name,
            'description' => $this->description,
            'budget' => $this->budget,
            'currency' => $this->currency,
            'start_date' => $this->startDate,
            'due_date' => $this->dueDate,
            'color' => $this->color,
        ];
    }
}
