<?php

namespace App\Livewire\Agency\Projects\Deliverables;

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
    use WithPagination;

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

    public function approve(string $uniqueId): void
    {
        $deliverable = $this->findDeliverable($uniqueId);

        if ($deliverable === null) {
            return;
        }

        $this->authorize('update', $deliverable);

        $this->deliverableService->approveDeliverable($deliverable, Auth::user());

        $this->dispatch('deliverable-created');
    }

    public function reject(string $uniqueId): void
    {
        $deliverable = $this->findDeliverable($uniqueId);

        if ($deliverable === null) {
            return;
        }

        $this->authorize('update', $deliverable);

        $this->deliverableService->rejectDeliverable($deliverable, Auth::user());

        $this->dispatch('deliverable-created');
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

    private function findDeliverable(string $uniqueId): ?Deliverable
    {
        return Deliverable::query()
            ->where('unique_id', $uniqueId)
            ->where('project_unique_id', $this->projectUniqueId)
            ->first();
    }

    public function render()
    {
        return view('livewire.agency.projects.deliverables.deliverables-list');
    }
}
