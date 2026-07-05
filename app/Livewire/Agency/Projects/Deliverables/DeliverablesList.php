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

    public ?string $submittingUniqueId = null;

    public ?string $deletingUniqueId = null;

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

    public function confirmSubmitForReview(string $uniqueId): void
    {
        $this->submittingUniqueId = $uniqueId;
        $this->modal('confirm-submit-deliverable')->show();
    }

    public function cancelSubmitForReview(): void
    {
        $this->submittingUniqueId = null;
    }

    public function confirmDeleteDeliverable(string $uniqueId): void
    {
        $this->deletingUniqueId = $uniqueId;
        $this->modal('confirm-delete-deliverable')->show();
    }

    public function cancelDeleteDeliverable(): void
    {
        $this->deletingUniqueId = null;
    }

    public function submitForReview(?string $uniqueId = null): void
    {
        $uniqueId ??= $this->submittingUniqueId;
        $this->submittingUniqueId = null;
        $this->modal('confirm-submit-deliverable')->close();

        if ($uniqueId === null) {
            return;
        }

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

    public function deleteDeliverable(?string $uniqueId = null): void
    {
        $uniqueId ??= $this->deletingUniqueId;
        $this->deletingUniqueId = null;
        $this->modal('confirm-delete-deliverable')->close();

        if ($uniqueId === null) {
            return;
        }

        $deliverable = $this->authorizeHubResource(
            'delete',
            $uniqueId,
            $this->projectUniqueId,
            $this->deliverableService->findDeliverableForProject(...),
        );

        if (! $deliverable instanceof Deliverable) {
            $this->notifyError(__('Deliverable not found.'));

            return;
        }

        $this->deliverableService->deleteDeliverable($deliverable, Auth::user());

        $this->notifySuccess(__('Deliverable deleted.'));

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
