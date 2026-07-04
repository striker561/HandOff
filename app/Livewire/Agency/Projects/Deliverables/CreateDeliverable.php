<?php

namespace App\Livewire\Agency\Projects\Deliverables;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
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

class CreateDeliverable extends Component
{
    use WithActionRateLimiting, WithFileUploads, WithNotifications;

    #[Locked]
    public ?string $projectUniqueId = null;

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

    #[On('open-create-deliverable')]
    public function open(string $projectUniqueId, ?string $milestoneUniqueId = null): void
    {
        $this->projectUniqueId = $projectUniqueId;
        $this->milestone_unique_id = $milestoneUniqueId ?? '';
        $this->reset('name', 'description', 'due_date', 'file');
        $this->type = DeliverableType::FILE->value;
        $this->resetValidation();
        $this->modal('create-deliverable')->show();
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

    public function create(): void
    {
        $this->authorize('create', Deliverable::class);

        if ($this->projectUniqueId === null) {
            return;
        }

        if (! $this->attemptRateLimitedAction('create-deliverable', maxAttempts: 10, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::enum(DeliverableType::class)],
            'milestone_unique_id' => ['required', 'string', 'exists:milestones,unique_id'],
            'due_date' => ['nullable', 'date'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $deliverable = $this->deliverableService->createDeliverable([
            'project_unique_id' => $this->projectUniqueId,
            'milestone_unique_id' => $validated['milestone_unique_id'],
            'created_by_unique_id' => Auth::user()->unique_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'due_date' => $validated['due_date'] ?? null,
        ], Auth::user());

        if ($this->file !== null) {
            $this->deliverableService->uploadFile($deliverable, $this->file, Auth::user());
        }

        $this->reset('name', 'description', 'milestone_unique_id', 'due_date', 'file');
        $this->type = DeliverableType::FILE->value;

        $this->modal('create-deliverable')->close();

        $this->notifySuccess(__('Deliverable created.'));

        $this->dispatch('deliverable-created');
    }

    public function render()
    {
        return view('livewire.agency.projects.deliverables.create-deliverable');
    }
}
