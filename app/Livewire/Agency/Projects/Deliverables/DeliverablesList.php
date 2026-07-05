<?php

namespace App\Livewire\Agency\Projects\Deliverables;

use App\Concerns\AuthorizesProjectHubResources;
use App\Concerns\WithNotifications;
use App\Models\Deliverable;
use App\Models\Milestone;
use App\Services\DeliverableService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class DeliverablesList extends Component
{
    use AuthorizesProjectHubResources, WithNotifications, WithPagination;

    #[Locked]
    public string $projectUniqueId;

    public ?string $milestoneUniqueId = null;

    private DeliverableService $deliverableService;

    public function boot(DeliverableService $deliverableService): void
    {
        $this->deliverableService = $deliverableService;
    }

    #[On('deliverable-created')]
    public function refreshDeliverables(): void
    {
        $this->resetPage();
    }

    public function openSaveDeliverable(): void
    {
        $this->dispatch('open-save-deliverable', projectUniqueId: $this->projectUniqueId, milestoneUniqueId: $this->milestoneUniqueId)
            ->to(SaveDeliverable::class);
    }

    public function editDeliverable(string $uniqueId): void
    {
        $this->dispatch('open-save-deliverable', projectUniqueId: $this->projectUniqueId, uniqueId: $uniqueId)
            ->to(SaveDeliverable::class);
    }

    public function submitForReview(string $uniqueId): void
    {
        $deliverable = $this->authorizeHubResource(
            'submitForReview',
            $uniqueId,
            $this->projectUniqueId,
            $this->deliverableService->findDeliverableForProject(...),
        );

        if (! $deliverable instanceof Deliverable) {
            $this->notifyError(__('Deliverable not found.'));

            return;
        }

        $this->deliverableService->submitForReview($deliverable, Auth::user());

        $this->notifySuccess(__('Deliverable submitted for client review.'));

        $this->resetPage();
    }

    #[Computed]
    public function hasMilestones(): bool
    {
        return Milestone::query()
            ->where('project_unique_id', $this->projectUniqueId)
            ->exists();
    }

    #[Computed]
    public function deliverables(): LengthAwarePaginator
    {
        $filters = [
            'sort' => 'order',
            'direction' => 'asc',
            'per_page' => 50,
        ];

        if ($this->milestoneUniqueId) {
            $filters['milestone_unique_id'] = $this->milestoneUniqueId;
        }

        return $this->deliverableService->getDeliverablesForProject($this->projectUniqueId, $filters);
    }

    public function render()
    {
        return view('livewire.agency.projects.deliverables.deliverables-list');
    }
}
