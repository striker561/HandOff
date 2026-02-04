<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\{Meeting, User};
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\Meeting\{MeetingStatus, MeetingLocation};

class MeetingService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return Meeting::class;
    }

    protected function searchableColumns(): array
    {
        return ['title', 'description'];
    }

    protected function filterableColumns(): array
    {
        return ['project_unique_id', 'deliverable_unique_id', 'status', 'scheduled_by_unique_id', 'location'];
    }

    protected function sortableColumns(): array
    {
        return ['title', 'status', 'scheduled_at', 'created_at', 'updated_at'];
    }

    public function scheduleMeeting(array $data, User $scheduler): Meeting
    {
        /** @var Meeting $meeting */
        $meeting = Meeting::create([
            'project_unique_id' => $data['project_unique_id'],
            'deliverable_unique_id' => $data['deliverable_unique_id'] ?? null,
            'scheduled_by_unique_id' => $scheduler->unique_id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'location' => $data['location'] ?? MeetingLocation::MEET,
            'status' => MeetingStatus::SCHEDULED,
            'metadata' => $data['metadata'] ?? [],
        ]);

        return $meeting;
    }

    public function rescheduleMeeting(
        Meeting $meeting,
        Carbon $newScheduledAt,
        ?int $newDuration = null
    ): Meeting {
        $meeting->update([
            'scheduled_at' => $newScheduledAt,
            'duration_minutes' => $newDuration ?? $meeting->duration_minutes,
            'status' => MeetingStatus::RESCHEDULED,
        ]);

        return $meeting->fresh();
    }

    public function completeMeeting(Meeting $meeting, ?string $notes = null): Meeting
    {
        $updateData = ['status' => MeetingStatus::COMPLETED];

        if ($notes) {
            $updateData['meeting_notes'] = $notes;
        }

        $meeting->update($updateData);

        return $meeting->fresh();
    }

    public function cancelMeeting(Meeting $meeting): Meeting
    {
        $meeting->update(['status' => MeetingStatus::CANCELLED]);
        return $meeting->fresh();
    }

    public function addMeetingNotes(Meeting $meeting, string $notes): Meeting
    {
        $meeting->update(['meeting_notes' => $notes]);
        return $meeting->fresh();
    }

    public function getMeetingsForProject(string $projectUniqueId, array $filters = []): LengthAwarePaginator
    {
        $query = Meeting::query()->where('project_unique_id', $projectUniqueId);
        $query = $this->applyFilters($query, $filters);
        return $this->paginateQuery($query, $filters);
    }

    public function getMeetingsForDeliverable(string $deliverableUniqueId, array $filters = []): LengthAwarePaginator
    {
        $query = Meeting::query()->where('deliverable_unique_id', $deliverableUniqueId);
        $query = $this->applyFilters($query, $filters);
        return $this->paginateQuery($query, $filters);
    }

    public function getMeetingsScheduledByUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Meeting::query()->where('scheduled_by_unique_id', $user->unique_id);
        $query = $this->applyFilters($query, $filters);
        return $this->paginateQuery($query, $filters);
    }

    public function isMeetingUpcoming(Meeting $meeting): bool
    {
        return $meeting->scheduled_at->isFuture()
            && $meeting->status === MeetingStatus::SCHEDULED;
    }

    public function getMeetingEndTime(Meeting $meeting): Carbon
    {
        return $meeting->scheduled_at->copy()->addMinutes($meeting->duration_minutes);
    }
}
