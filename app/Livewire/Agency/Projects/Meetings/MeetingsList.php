<?php

namespace App\Livewire\Agency\Projects\Meetings;

use App\Services\MeetingService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MeetingsList extends Component
{
    use WithPagination;

    #[Locked]
    public string $projectUniqueId;

    private MeetingService $meetingService;

    public function boot(MeetingService $meetingService): void
    {
        $this->meetingService = $meetingService;
    }

    #[On('meeting-scheduled')]
    #[On('meeting-updated')]
    public function refreshMeetings(): void
    {
        $this->resetPage();
    }

    public function openScheduleMeeting(): void
    {
        $this->dispatch('open-save-meeting', projectUniqueId: $this->projectUniqueId)
            ->to(SaveMeeting::class);
    }

    public function editMeeting(string $uniqueId): void
    {
        $this->dispatch('open-save-meeting', projectUniqueId: $this->projectUniqueId, uniqueId: $uniqueId)
            ->to(SaveMeeting::class);
    }

    #[Computed]
    public function meetings(): LengthAwarePaginator
    {
        return $this->meetingService->getMeetingsForProject($this->projectUniqueId, [
            'sort' => 'scheduled_at',
            'direction' => 'desc',
            'per_page' => 50,
        ]);
    }

    public function render()
    {
        return view('livewire.agency.projects.meetings.meetings-list');
    }
}
