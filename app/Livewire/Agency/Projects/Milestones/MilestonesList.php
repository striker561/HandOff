<?php

namespace App\Livewire\Agency\Projects\Milestones;

use App\Concerns\AuthorizesProjectHubResources;
use App\Concerns\WithNotifications;
use App\Models\Milestone;
use App\Services\MilestoneService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MilestonesList extends Component
{
    use AuthorizesProjectHubResources, WithNotifications, WithPagination;

    #[Locked]
    public string $projectUniqueId;

    public ?string $deletingUniqueId = null;

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

    public function openSaveMilestone(): void
    {
        $this->dispatch('open-save-milestone', projectUniqueId: $this->projectUniqueId)
            ->to(SaveMilestone::class);
    }

    public function editMilestone(string $uniqueId): void
    {
        $this->dispatch('open-save-milestone', projectUniqueId: $this->projectUniqueId, uniqueId: $uniqueId)
            ->to(SaveMilestone::class);
    }

    public function confirmDeleteMilestone(string $uniqueId): void
    {
        $this->deletingUniqueId = $uniqueId;
        $this->modal('confirm-delete-milestone')->show();
    }

    public function cancelDeleteMilestone(): void
    {
        $this->deletingUniqueId = null;
    }

    public function deleteMilestone(?string $uniqueId = null): void
    {
        $uniqueId ??= $this->deletingUniqueId;
        $this->deletingUniqueId = null;
        $this->modal('confirm-delete-milestone')->close();

        if ($uniqueId === null) {
            return;
        }

        $milestone = $this->authorizeHubResource(
            'delete',
            $uniqueId,
            $this->projectUniqueId,
            $this->milestoneService->findMilestoneForProject(...),
        );

        if (! $milestone instanceof Milestone) {
            $this->notifyError(__('Milestone not found.'));

            return;
        }

        if (! $this->milestoneService->deleteMilestone($milestone, Auth::user())) {
            $this->notifyError(__('This milestone cannot be deleted.'));

            return;
        }

        $this->notifySuccess(__('Milestone deleted.'));

        $this->resetPage();
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
