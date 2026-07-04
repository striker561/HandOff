<?php

namespace App\Livewire\Agency\Projects\Milestones;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Models\Milestone;
use App\Services\MilestoneService;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateMilestone extends Component
{
    use WithActionRateLimiting, WithNotifications;

    #[Locked]
    public ?string $projectUniqueId = null;

    public string $name = '';

    public string $description = '';

    public ?string $due_date = null;

    private MilestoneService $milestoneService;

    public function boot(MilestoneService $milestoneService): void
    {
        $this->milestoneService = $milestoneService;
    }

    #[On('open-create-milestone')]
    public function open(string $projectUniqueId): void
    {
        $this->projectUniqueId = $projectUniqueId;
        $this->reset('name', 'description', 'due_date');
        $this->resetValidation();
        $this->modal('create-milestone')->show();
    }

    public function create(): void
    {
        $this->authorize('create', Milestone::class);

        if ($this->projectUniqueId === null) {
            return;
        }

        if (! $this->attemptRateLimitedAction('create-milestone', maxAttempts: 10, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
        ]);

        $this->milestoneService->createOrderedMilestone([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'project_unique_id' => $this->projectUniqueId,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        $this->reset('name', 'description', 'due_date');

        $this->modal('create-milestone')->close();

        $this->notifySuccess(__('Milestone created.'));

        $this->dispatch('milestone-created');
    }

    public function render()
    {
        return view('livewire.agency.projects.milestones.create-milestone');
    }
}
