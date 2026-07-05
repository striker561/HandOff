<?php

namespace App\Livewire\Agency\Projects\Deliverables;

use App\Concerns\AuthorizesProjectHubResources;
use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Data\Deliverables\SaveDeliverableData;
use App\Enums\Deliverable\DeliverableType;
use App\Models\Deliverable;
use App\Services\DeliverableFileService;
use App\Services\DeliverableService;
use App\Services\MilestoneService;
use App\Services\ProjectService;
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
    use AuthorizesProjectHubResources, WithActionRateLimiting, WithFileUploads, WithNotifications;

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

    public string $link = '';

    public string $content = '';

    private DeliverableService $deliverableService;

    private DeliverableFileService $deliverableFileService;

    private MilestoneService $milestoneService;

    private ProjectService $projectService;

    public function boot(
        DeliverableService $deliverableService,
        DeliverableFileService $deliverableFileService,
        MilestoneService $milestoneService,
        ProjectService $projectService,
    ): void {
        $this->deliverableService = $deliverableService;
        $this->deliverableFileService = $deliverableFileService;
        $this->milestoneService = $milestoneService;
        $this->projectService = $projectService;
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
        $this->reset('name', 'description', 'due_date', 'file', 'link', 'content');
        $this->milestone_unique_id = $milestoneUniqueId ?? '';
        $this->type = DeliverableType::FILE->value;
        $this->resetValidation();

        $deliverable = $this->viewHubResource(
            $uniqueId,
            $projectUniqueId,
            $this->deliverableService->findDeliverableForProject(...),
        );

        if ($uniqueId !== null && $deliverable === null) {
            $this->notifyError(__('Deliverable not found.'));

            return;
        }

        if ($deliverable instanceof Deliverable) {
            $this->name = $deliverable->name;
            $this->description = $deliverable->description ?? '';
            $this->type = $deliverable->type->value;
            $this->milestone_unique_id = $deliverable->milestone_unique_id ?? '';
            $this->due_date = $deliverable->due_date?->format('Y-m-d');
            $this->link = $deliverable->metadata['link'] ?? '';
            $this->content = $deliverable->metadata['content'] ?? '';
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

    #[Computed]
    public function currentType(): ?DeliverableType
    {
        return DeliverableType::tryFrom($this->type);
    }

    public function save(): void
    {
        if ($this->projectUniqueId === null) {
            return;
        }

        $deliverable = null;

        if ($this->isEditing()) {
            $deliverable = $this->authorizeHubResource(
                'update',
                $this->uniqueId,
                $this->projectUniqueId,
                $this->deliverableService->findDeliverableForProject(...),
            );

            if ($deliverable === null) {
                $this->notifyError(__('Deliverable not found.'));

                return;
            }
        } elseif (! $this->authorizeHubResourceCreate(Deliverable::class, $this->projectUniqueId, $this->projectService)) {
            return;
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

        $currentType = $this->currentType();

        if ($currentType?->isFileBased() && ! $this->isEditing()) {
            $rules['file'] = ['nullable', 'file', 'max:10240'];
        }

        if ($currentType?->isLink()) {
            $rules['link'] = $this->isEditing()
                ? ['nullable', 'url', 'max:2048']
                : ['required', 'url', 'max:2048'];
        }

        if ($currentType?->isTextBased()) {
            $rules['content'] = $this->isEditing()
                ? ['nullable', 'string', 'max:50000']
                : ['required', 'string', 'max:50000'];
        }

        $validated = $this->validate($rules);

        $metadata = [];

        if ($currentType?->isLink() && ! empty($validated['link'])) {
            $metadata['link'] = $validated['link'];
        }

        if ($currentType?->isTextBased() && ! empty($validated['content'])) {
            $metadata['content'] = $validated['content'];
        }

        $data = SaveDeliverableData::fromArray([
            'project_unique_id' => $this->projectUniqueId,
            'milestone_unique_id' => $validated['milestone_unique_id'],
            'created_by_unique_id' => Auth::user()->unique_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'due_date' => $validated['due_date'] ?? null,
            'metadata' => $metadata,
        ]);

        if ($this->isEditing()) {
            $this->deliverableService->updateDeliverable($deliverable, $data, Auth::user());
            $this->notifySuccess(__('Deliverable updated.'));
        } else {
            $deliverable = $this->deliverableService->createDeliverable($data, Auth::user());

            if ($this->file !== null) {
                $this->authorize('uploadFile', $deliverable);
                $this->deliverableFileService->uploadFile($deliverable, $this->file, Auth::user());
            }

            $this->notifySuccess(__('Deliverable created.'));
        }

        $this->reset('name', 'description', 'milestone_unique_id', 'due_date', 'file', 'link', 'content', 'uniqueId');
        $this->type = DeliverableType::FILE->value;

        $this->modal('save-deliverable')->close();

        $this->dispatch('deliverable-created');
    }

    public function render()
    {
        return view('livewire.agency.projects.deliverables.save-deliverable');
    }
}
