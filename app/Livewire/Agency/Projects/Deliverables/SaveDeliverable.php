<?php

namespace App\Livewire\Agency\Projects\Deliverables;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Data\Deliverables\SaveDeliverableData;
use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Deliverable\DeliverableType;
use App\Models\Deliverable;
use App\Services\DeliverableService;
use App\Services\MilestoneService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class SaveDeliverable extends Component
{
    use WithActionRateLimiting, WithFileUploads, WithNotifications;

    #[Locked]
    public ?string $projectUniqueId = null;

    #[Locked]
    public ?string $uniqueId = null;

    public string $name = '';

    public string $description = '';

    public string $type = 'file';

    public string $milestone_unique_id = '';

    public ?string $due_date = null;

    public $file = null;

    private DeliverableService $deliverableService;

    private MilestoneService $milestoneService;

    public function boot(DeliverableService $deliverableService, MilestoneService $milestoneService): void
    {
        $this->deliverableService = $deliverableService;
        $this->milestoneService = $milestoneService;
    }

    #[Computed]
    public function isEditing(): bool
    {
        return $this->uniqueId !== null;
    }

    #[On('open-save-deliverable')]
    public function open(string $projectUniqueId, ?string $uniqueId = null, ?string $milestoneUniqueId = null): void
    {
        $this->projectUniqueId = $projectUniqueId;
        $this->uniqueId = $uniqueId;
        $this->reset('name', 'description', 'due_date', 'file');
        $this->milestone_unique_id = $milestoneUniqueId ?? '';
        $this->type = DeliverableType::FILE->value;
        $this->resetValidation();

        if ($uniqueId !== null) {
            $deliverable = $this->findDeliverable($uniqueId, $projectUniqueId);

            if ($deliverable === null) {
                $this->notifyError(__('Deliverable not found.'));

                return;
            }

            if ($deliverable->status === DeliverableStatus::APPROVED) {
                $this->notifyError(__('Approved deliverables cannot be edited.'));

                return;
            }

            $this->authorize('update', $deliverable);

            $this->name = $deliverable->name;
            $this->description = $deliverable->description ?? '';
            $this->type = $deliverable->type->value;
            $this->milestone_unique_id = $deliverable->milestone_unique_id ?? '';
            $this->due_date = $deliverable->due_date?->format('Y-m-d');
        } else {
            $this->authorize('create', Deliverable::class);
        }

        $this->modal('save-deliverable')->show();
    }

    #[Computed]
    public function milestones(): LengthAwarePaginator
    {
        if ($this->projectUniqueId === null) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }

        return $this->milestoneService->getMilestonesForProject($this->projectUniqueId, [
            'sort' => 'order',
            'direction' => 'asc',
            'per_page' => 100,
        ]);
    }

    #[Computed]
    public function deliverableTypes(): Collection
    {
        return collect(DeliverableType::cases());
    }

    public function save(): void
    {
        if ($this->projectUniqueId === null) {
            return;
        }

        if ($this->isEditing()) {
            $deliverable = $this->findDeliverable($this->uniqueId, $this->projectUniqueId);

            if ($deliverable === null) {
                $this->notifyError(__('Deliverable not found.'));

                return;
            }

            $this->authorize('update', $deliverable);
        } else {
            $this->authorize('create', Deliverable::class);
        }

        if (! $this->attemptRateLimitedAction('save-deliverable', maxAttempts: 10, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::enum(DeliverableType::class)],
            'milestone_unique_id' => ['required', 'string', 'exists:milestones,unique_id'],
            'due_date' => ['nullable', 'date'],
        ];

        if (! $this->isEditing()) {
            $rules['file'] = ['nullable', 'file', 'max:10240'];
        }

        $validated = $this->validate($rules);

        $data = SaveDeliverableData::fromArray([
            'project_unique_id' => $this->projectUniqueId,
            'milestone_unique_id' => $validated['milestone_unique_id'],
            'created_by_unique_id' => Auth::user()->unique_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'due_date' => $validated['due_date'] ?? null,
        ]);

        if ($this->isEditing()) {
            $deliverable = $this->findDeliverable($this->uniqueId, $this->projectUniqueId);
            $this->deliverableService->updateDeliverable($deliverable, $data, Auth::user());
            $this->notifySuccess(__('Deliverable updated.'));
        } else {
            $deliverable = $this->deliverableService->createDeliverable($data, Auth::user());

            if ($this->file !== null) {
                $this->deliverableService->uploadFile($deliverable, $this->file, Auth::user());
            }

            $this->notifySuccess(__('Deliverable created.'));
        }

        $this->reset('name', 'description', 'milestone_unique_id', 'due_date', 'file', 'uniqueId');
        $this->type = DeliverableType::FILE->value;

        $this->modal('save-deliverable')->close();

        $this->dispatch('deliverable-created');
    }

    private function findDeliverable(string $uniqueId, string $projectUniqueId): ?Deliverable
    {
        return Deliverable::query()
            ->where('unique_id', $uniqueId)
            ->where('project_unique_id', $projectUniqueId)
            ->first();
    }

    public function render()
    {
        return view('livewire.agency.projects.deliverables.save-deliverable');
    }
}
