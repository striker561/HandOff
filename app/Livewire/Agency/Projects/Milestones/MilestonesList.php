<?php

namespace App\Livewire\Agency\Projects\Milestones;

use App\Services\MilestoneService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MilestonesList extends Component
{
    use WithPagination;

    #[Locked]
    public string $projectUniqueId;

    private MilestoneService $milestoneService;

    public function boot(MilestoneService $milestoneService): void
    {
        $this->milestoneService = $milestoneService;
    }

    #[On('milestone-created')]
    public function refreshMilestones(): void
    {
        $this->resetPage();
    }

    public function openCreateMilestone(): void
    {
        $this->dispatch('open-create-milestone', projectUniqueId: $this->projectUniqueId)
            ->to(CreateMilestone::class);
    }

    #[Computed]
    public function milestones(): LengthAwarePaginator
    {
        return $this->milestoneService->getMilestonesForProject($this->projectUniqueId, [
            'sort' => 'order',
            'direction' => 'asc',
            'per_page' => 50,
        ]);
    }

    public function render()
    {
        return view('livewire.agency.projects.milestones.milestones-list');
    }
}
