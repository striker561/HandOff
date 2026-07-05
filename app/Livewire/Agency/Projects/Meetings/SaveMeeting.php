<?php

namespace App\Livewire\Agency\Projects\Meetings;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Data\Meetings\SaveMeetingData;
use App\Enums\Meeting\MeetingLocation;
use App\Enums\Meeting\MeetingStatus;
use App\Models\Meeting;
use App\Services\DeliverableService;
use App\Services\MeetingService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class SaveMeeting extends Component
{
    use WithActionRateLimiting, WithNotifications;

    #[Locked]
    public ?string $projectUniqueId = null;

    #[Locked]
    public ?string $uniqueId = null;

    #[Locked]
    public ?string $originalScheduledAt = null;

    public string $title = '';

    public string $description = '';

    public ?string $scheduled_at = null;

    public int $duration_minutes = 60;

    public string $location = 'meet';

    public string $deliverable_unique_id = '';

    private MeetingService $meetingService;

    private DeliverableService $deliverableService;

    public function boot(MeetingService $meetingService, DeliverableService $deliverableService): void
    {
        $this->meetingService = $meetingService;
        $this->deliverableService = $deliverableService;
    }

    #[Computed]
    public function isEditing(): bool
    {
        return $this->uniqueId !== null;
    }

    #[On('open-save-meeting')]
    public function open(string $projectUniqueId, ?string $uniqueId = null): void
    {
        $this->projectUniqueId = $projectUniqueId;
        $this->uniqueId = $uniqueId;
        $this->originalScheduledAt = null;
        $this->reset('title', 'description', 'scheduled_at', 'deliverable_unique_id');
        $this->duration_minutes = 60;
        $this->location = MeetingLocation::MEET->value;
        $this->resetValidation();

        if ($uniqueId !== null) {
            $meeting = $this->findMeeting($uniqueId, $projectUniqueId);

            if ($meeting === null) {
                $this->notifyError(__('Meeting not found.'));

                return;
            }

            if ($meeting->status !== MeetingStatus::SCHEDULED) {
                $this->notifyError(__('Only scheduled meetings can be edited.'));

                return;
            }

            $this->authorize('update', $meeting);

            $this->title = $meeting->title;
            $this->description = $meeting->description ?? '';
            $this->scheduled_at = $meeting->scheduled_at?->format('Y-m-d\TH:i');
            $this->originalScheduledAt = $this->scheduled_at;
            $this->duration_minutes = $meeting->duration_minutes;
            $this->location = $meeting->location->value;
            $this->deliverable_unique_id = $meeting->deliverable_unique_id ?? '';
        } else {
            $this->authorize('create', Meeting::class);
        }

        $this->modal('save-meeting')->show();
    }

    #[Computed]
    public function deliverables(): LengthAwarePaginator
    {
        if ($this->projectUniqueId === null) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }

        return $this->deliverableService->getDeliverablesForProject($this->projectUniqueId, [
            'sort' => 'name',
            'direction' => 'asc',
            'per_page' => 100,
        ]);
    }

    #[Computed]
    public function meetingLocations(): Collection
    {
        return collect(MeetingLocation::cases());
    }

    public function save(): void
    {
        if ($this->projectUniqueId === null) {
            return;
        }

        if ($this->isEditing()) {
            $meeting = $this->findMeeting($this->uniqueId, $this->projectUniqueId);

            if ($meeting === null) {
                $this->notifyError(__('Meeting not found.'));

                return;
            }

            $this->authorize('update', $meeting);
        } else {
            $this->authorize('create', Meeting::class);
        }

        if (! $this->attemptRateLimitedAction('save-meeting', maxAttempts: 10, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $scheduledAtRules = ['required', 'date'];

        if (! $this->isEditing()) {
            $scheduledAtRules[] = 'after:now';
        } elseif ($this->scheduled_at !== $this->originalScheduledAt) {
            $scheduledAtRules[] = 'after:now';
        }

        $validated = $this->validate([
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'scheduled_at' => $scheduledAtRules,
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'location' => ['required', Rule::enum(MeetingLocation::class)],
            'deliverable_unique_id' => ['nullable', 'string', 'exists:deliverables,unique_id'],
        ]);

        $data = SaveMeetingData::fromArray([
            'project_unique_id' => $this->projectUniqueId,
            'deliverable_unique_id' => $validated['deliverable_unique_id'] ?: null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => Carbon::parse($validated['scheduled_at'])->toDateTimeString(),
            'duration_minutes' => $validated['duration_minutes'],
            'location' => $validated['location'],
        ]);

        if ($this->isEditing()) {
            $meeting = $this->findMeeting($this->uniqueId, $this->projectUniqueId);
            $this->meetingService->updateMeeting($meeting, $data, Auth::user());
            $this->notifySuccess(__('Meeting updated.'));
            $this->dispatch('meeting-updated');
        } else {
            $this->meetingService->scheduleMeeting($data, Auth::user());
            $this->notifySuccess(__('Meeting scheduled.'));
            $this->dispatch('meeting-scheduled');
        }

        $this->reset('title', 'description', 'scheduled_at', 'deliverable_unique_id', 'uniqueId', 'originalScheduledAt');
        $this->duration_minutes = 60;
        $this->location = MeetingLocation::MEET->value;

        $this->modal('save-meeting')->close();
    }

    private function findMeeting(string $uniqueId, string $projectUniqueId): ?Meeting
    {
        return Meeting::query()
            ->where('unique_id', $uniqueId)
            ->where('project_unique_id', $projectUniqueId)
            ->first();
    }

    public function render()
    {
        return view('livewire.agency.projects.meetings.save-meeting');
    }
}
