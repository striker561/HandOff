<?php

namespace App\Data\Meetings;

use App\Enums\Meeting\MeetingLocation;

readonly class SaveMeetingData
{
    public function __construct(
        public string $projectUniqueId,
        public string $title,
        public ?string $description,
        public string $scheduledAt,
        public int $durationMinutes,
        public MeetingLocation $location,
        public ?string $deliverableUniqueId,
        public array $metadata = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            projectUniqueId: $data['project_unique_id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            scheduledAt: $data['scheduled_at'],
            durationMinutes: (int) ($data['duration_minutes'] ?? 60),
            location: MeetingLocation::from($data['location']),
            deliverableUniqueId: isset($data['deliverable_unique_id']) && $data['deliverable_unique_id'] !== ''
                ? $data['deliverable_unique_id']
                : null,
            metadata: $data['metadata'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toCreateAttributes(string $scheduledByUniqueId): array
    {
        return [
            'project_unique_id' => $this->projectUniqueId,
            'deliverable_unique_id' => $this->deliverableUniqueId,
            'scheduled_by_unique_id' => $scheduledByUniqueId,
            'title' => $this->title,
            'description' => $this->description,
            'scheduled_at' => $this->scheduledAt,
            'duration_minutes' => $this->durationMinutes,
            'location' => $this->location,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toUpdateAttributes(): array
    {
        return [
            'deliverable_unique_id' => $this->deliverableUniqueId,
            'title' => $this->title,
            'description' => $this->description,
            'scheduled_at' => $this->scheduledAt,
            'duration_minutes' => $this->durationMinutes,
            'location' => $this->location,
            'metadata' => $this->metadata,
        ];
    }
}
