<?php

namespace App\Data\Projects;

readonly class ProjectOverviewStats
{
    public function __construct(
        public int $deliverablesTotal,
        public int $deliverablesApproved,
        public int $credentialsTotal,
        public int $meetingsTotal,
        public int $meetingsUpcoming,
    ) {}

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'deliverables_total' => $this->deliverablesTotal,
            'deliverables_approved' => $this->deliverablesApproved,
            'credentials_total' => $this->credentialsTotal,
            'meetings_total' => $this->meetingsTotal,
            'meetings_upcoming' => $this->meetingsUpcoming,
        ];
    }

    /**
     * @param  array<string, int>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            deliverablesTotal: (int) $data['deliverables_total'],
            deliverablesApproved: (int) $data['deliverables_approved'],
            credentialsTotal: (int) $data['credentials_total'],
            meetingsTotal: (int) $data['meetings_total'],
            meetingsUpcoming: (int) $data['meetings_upcoming'],
        );
    }
}
