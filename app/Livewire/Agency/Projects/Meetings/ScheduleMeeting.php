<?php

namespace App\Livewire\Agency\Projects\Meetings;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Enums\Meeting\MeetingLocation;
use App\Models\Meeting;
use App\Services\DeliverableService;
use App\Services\MeetingService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class ScheduleMeeting extends Component
{
    use WithActionRateLimiting, WithNotifications;

    #[Locked]
    public ?string $projectUniqueId = null;

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

    #[On('open-schedule-meeting')]
    public function open(string $projectUniqueId): void
    {
        $this->projectUniqueId = $projectUniqueId;
        $this->reset('title', 'description', 'scheduled_at', 'deliverable_unique_id');
        $this->duration_minutes = 60;
        $this->location = MeetingLocation::MEET->value;
        $this->resetValidation();
        $this->modal('schedule-meeting')->show();
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

    public function schedule(): void
    {
        $this->authorize('create', Meeting::class);

        if ($this->projectUniqueId === null) {
            return;
        }

        if (! $this->attemptRateLimitedAction('schedule-meeting', maxAttempts: 10, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $validated = $this->validate([
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'location' => ['required', Rule::enum(MeetingLocation::class)],
            'deliverable_unique_id' => ['nullable', 'string', 'exists:deliverables,unique_id'],
        ]);

        $this->meetingService->scheduleMeeting([
            'project_unique_id' => $this->projectUniqueId,
            'deliverable_unique_id' => $validated['deliverable_unique_id'] ?: null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'duration_minutes' => $validated['duration_minutes'],
            'location' => $validated['location'],
        ], Auth::user());

        $this->reset('title', 'description', 'scheduled_at', 'deliverable_unique_id');
        $this->duration_minutes = 60;
        $this->location = MeetingLocation::MEET->value;

        $this->modal('schedule-meeting')->close();

        $this->notifySuccess(__('Meeting scheduled.'));

        $this->dispatch('meeting-scheduled');
    }

    public function render()
    {
        return view('livewire.agency.projects.meetings.schedule-meeting');
    }
}
