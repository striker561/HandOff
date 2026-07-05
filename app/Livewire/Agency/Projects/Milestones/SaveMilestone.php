<?php

namespace App\Livewire\Agency\Projects\Milestones;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Data\Milestones\SaveMilestoneData;
use App\Enums\Milestone\MilestoneStatus;
use App\Models\Milestone;
use App\Services\MilestoneService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class SaveMilestone extends Component
{
    use WithActionRateLimiting, WithNotifications;

    #[Locked]
    public ?string $projectUniqueId = null;

    #[Locked]
    public ?string $uniqueId = null;

    public string $name = '';

    public string $description = '';

    public ?string $due_date = null;

    public string $status = '';

    private MilestoneService $milestoneService;

    public function boot(MilestoneService $milestoneService): void
    {
        $this->milestoneService = $milestoneService;
    }

    #[Computed]
    public function isEditing(): bool
    {
        return $this->uniqueId !== null;
    }

    #[Computed]
    public function isStatusLocked(): bool
    {
        return $this->isEditing()
            && MilestoneStatus::tryFrom($this->status) === MilestoneStatus::COMPLETED;
    }

    #[On('open-save-milestone')]
    public function open(string $projectUniqueId, ?string $uniqueId = null): void
    {
        $this->projectUniqueId = $projectUniqueId;
        $this->uniqueId = $uniqueId;
        $this->reset('name', 'description', 'due_date', 'status');
        $this->resetValidation();

        if ($uniqueId !== null) {
            $milestone = $this->findMilestone($uniqueId, $projectUniqueId);

            if ($milestone === null) {
                $this->notifyError(__('Milestone not found.'));

                return;
            }

            $this->authorize('update', $milestone);

            $this->name = $milestone->name;
            $this->description = $milestone->description ?? '';
            $this->due_date = $milestone->due_date?->format('Y-m-d');
            $this->status = $milestone->status->value;
        } else {
            $this->authorize('create', Milestone::class);

            $this->status = MilestoneStatus::PENDING->value;
        }

        $this->modal('save-milestone')->show();
    }

    public function save(): void
    {
        if ($this->projectUniqueId === null) {
            return;
        }

        if ($this->isEditing()) {
            $milestone = $this->findMilestone($this->uniqueId, $this->projectUniqueId);

            if ($milestone === null) {
                $this->notifyError(__('Milestone not found.'));

                return;
            }

            $this->authorize('update', $milestone);
        } else {
            $this->authorize('create', Milestone::class);
        }

        if (! $this->attemptRateLimitedAction('save-milestone', maxAttempts: 10, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
        ];

        if (! $this->isStatusLocked) {
            $rules['status'] = [
                'required',
                'string',
                Rule::in(collect(MilestoneStatus::selectable())->map->value->all()),
            ];
        }

        $validated = $this->validate($rules);

        $data = SaveMilestoneData::fromArray([
            'project_unique_id' => $this->projectUniqueId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'status' => $this->isStatusLocked ? null : $validated['status'],
        ]);

        if ($this->isEditing()) {
            $milestone = $this->findMilestone($this->uniqueId, $this->projectUniqueId);
            $this->milestoneService->updateMilestone($milestone, $data, Auth::user());
            $this->notifySuccess(__('Milestone updated.'));
        } else {
            $this->milestoneService->createOrderedMilestone($data, Auth::user());
            $this->notifySuccess(__('Milestone created.'));
        }

        $this->reset('name', 'description', 'due_date', 'status', 'uniqueId');

        $this->modal('save-milestone')->close();

        $this->dispatch('milestone-created');
    }

    private function findMilestone(string $uniqueId, string $projectUniqueId): ?Milestone
    {
        return Milestone::query()
            ->where('unique_id', $uniqueId)
            ->where('project_unique_id', $projectUniqueId)
            ->first();
    }

    public function render()
    {
        return view('livewire.agency.projects.milestones.save-milestone');
    }
}
