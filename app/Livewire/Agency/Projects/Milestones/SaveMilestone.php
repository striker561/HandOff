<?php

namespace App\Livewire\Agency\Projects\Milestones;

use App\Concerns\AuthorizesProjectHubResources;
use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Data\Milestones\SaveMilestoneData;
use App\Enums\Milestone\MilestoneStatus;
use App\Models\Milestone;
use App\Services\MilestoneService;
use App\Services\ProjectService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class SaveMilestone extends Component
{
    use AuthorizesProjectHubResources, WithActionRateLimiting, WithNotifications;

    #[Locked]
    public ?string $projectUniqueId = null;

    #[Locked]
    public ?string $uniqueId = null;

    public string $name = '';

    public string $description = '';

    public ?string $due_date = null;

    public string $status = '';

    private MilestoneService $milestoneService;

    private ProjectService $projectService;

    public function boot(MilestoneService $milestoneService, ProjectService $projectService): void
    {
        $this->milestoneService = $milestoneService;
        $this->projectService = $projectService;
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

        $milestone = $this->viewHubResource(
            $uniqueId,
            $projectUniqueId,
            $this->milestoneService->findMilestoneForProject(...),
        );

        if ($uniqueId !== null && $milestone === null) {
            $this->notifyError(__('Milestone not found.'));

            return;
        }

        if ($milestone instanceof Milestone) {
            $this->name = $milestone->name;
            $this->description = $milestone->description ?? '';
            $this->due_date = $milestone->due_date?->format('Y-m-d');
            $this->status = $milestone->status->value;
        } else {
            $this->status = MilestoneStatus::PENDING->value;
        }

        $this->modal('save-milestone')->show();
    }

    public function save(): void
    {
        if ($this->projectUniqueId === null) {
            return;
        }

        $milestone = null;

        if ($this->isEditing()) {
            $milestone = $this->authorizeHubResource(
                'update',
                $this->uniqueId,
                $this->projectUniqueId,
                $this->milestoneService->findMilestoneForProject(...),
            );

            if ($milestone === null) {
                $this->notifyError(__('Milestone not found.'));

                return;
            }
        } elseif (! $this->authorizeHubResourceCreate(Milestone::class, $this->projectUniqueId, $this->projectService)) {
            return;
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

        if (! $this->isStatusLocked()) {
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
            'status' => $this->isStatusLocked() ? null : $validated['status'],
        ]);

        if ($this->isEditing()) {
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

    public function render()
    {
        return view('livewire.agency.projects.milestones.save-milestone');
    }
}
