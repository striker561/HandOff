<?php

namespace App\Livewire\Agency\Projects\Deliverables;

use App\Concerns\AuthorizesProjectHubResources;
use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Data\Deliverables\SaveDeliverableData;
use App\Enums\Deliverable\DeliverableType;
use App\Livewire\Ui\FileUploader;
use App\Models\Deliverable;
use App\Models\DeliverableFile;
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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class SaveDeliverable extends Component
{
    use AuthorizesProjectHubResources, WithActionRateLimiting, WithFileUploads, WithNotifications;

    #[Locked]
    public ?string $projectUniqueId = null;

    #[Locked]
    public ?string $uniqueId = null;

    #[Locked]
    public bool $readOnly = false;

    #[Locked]
    public string $statusLabel = '';

    #[Locked]
    public string $statusBadgeColor = 'gray';

    public string $name = '';

    public string $description = '';

    public string $type = 'file';

    public string $milestone_unique_id = '';

    public ?string $due_date = null;

    /**
     * @var array{
     *     existing: list<array{id: string, label: string, size?: int}>,
     *     pending: list<mixed>,
     *     removed_ids: list<string>
     * }
     */
    public array $fileUploaderState = [
        'existing' => [],
        'pending' => [],
        'removed_ids' => [],
    ];

    public int $fileUploaderKey = 0;

    /**
     * Top-level uploads on the parent avoid Flux/Livewire morph issues in modals.
     *
     * @var list<TemporaryUploadedFile>
     */
    public array $pendingDeliverableFiles = [];

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
        $this->readOnly = false;
        $this->statusLabel = '';
        $this->statusBadgeColor = 'gray';
        $this->reset('name', 'description', 'due_date', 'link', 'content', 'pendingDeliverableFiles');
        $this->fileUploaderState = $this->emptyFileUploaderState();
        $this->fileUploaderKey++;
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
            $this->statusLabel = $deliverable->status->label();
            $this->statusBadgeColor = $deliverable->status->badgeColor();
            $this->readOnly = ! $deliverable->status->isAgencyEditable();
            $this->loadExistingFiles($deliverable);
        }

        $this->modal('save-deliverable')->show();
    }

    #[On('file-uploader-updated')]
    public function syncFileUploaderState(array $state): void
    {
        $this->fileUploaderState['existing'] = $state['existing'] ?? [];
        $this->fileUploaderState['removed_ids'] = $state['removed_ids'] ?? [];
    }

    public function updatedPendingDeliverableFiles(): void
    {
        if ($this->pendingDeliverableFiles === []) {
            $this->fileUploaderState['pending'] = [];

            return;
        }

        $this->validate(
            FileUploader::rulesForPendingUploads(
                $this->fileUploaderState,
                'pendingDeliverableFiles',
                maxFiles: $this->deliverableMaxFiles,
            ),
            FileUploader::messagesForPendingUploads(
                'pendingDeliverableFiles',
                maxFiles: $this->deliverableMaxFiles,
            ),
        );

        $this->fileUploaderState['pending'] = $this->pendingDeliverableFiles;
    }

    public function removePendingDeliverableFile(int $index): void
    {
        unset($this->pendingDeliverableFiles[$index]);
        $this->pendingDeliverableFiles = array_values($this->pendingDeliverableFiles);
        $this->fileUploaderState['pending'] = $this->pendingDeliverableFiles;
        $this->resetValidation('pendingDeliverableFiles', 'pendingDeliverableFiles.*');
    }

    public function updatedType(): void
    {
        $type = $this->currentType();

        if (! $type?->isFileBased()) {
            $this->fileUploaderState = $this->emptyFileUploaderState();
            $this->pendingDeliverableFiles = [];
            $this->fileUploaderKey++;
        }

        if (! $type?->isLink()) {
            $this->link = '';
        }

        if (! $type?->isTextBased()) {
            $this->content = '';
        }

        $this->resetValidation('link', 'content', 'pendingDeliverableFiles', 'pendingDeliverableFiles.*', 'fileUploaderState.pending', 'fileUploaderState.pending.*');
    }

    #[Computed]
    public function showFileUploader(): bool
    {
        return $this->currentType()?->isFileBased() ?? false;
    }

    #[Computed]
    public function canUploadDeliverableFile(): bool
    {
        if (! $this->showFileUploader) {
            return false;
        }

        if (! $this->isEditing()) {
            return true;
        }

        if ($this->projectUniqueId === null || $this->uniqueId === null) {
            return false;
        }

        $deliverable = $this->deliverableService->findDeliverableForProject(
            $this->uniqueId,
            $this->projectUniqueId,
        );

        return $deliverable !== null && $deliverable->status->isAgencyEditable();
    }

    #[Computed]
    public function deliverableMaxFiles(): int
    {
        return (int) config('handoff.deliverables.max_files', FileUploader::defaultMaxFiles());
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
        if ($this->projectUniqueId === null || $this->readOnly) {
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

        if ($currentType?->isFileBased() && $this->fileUploaderState['pending'] !== []) {
            $rules = array_merge(
                $rules,
                FileUploader::rulesForPendingUploads(
                    $this->fileUploaderState,
                    'pendingDeliverableFiles',
                    maxFiles: $this->deliverableMaxFiles,
                ),
            );
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

        $validated = $this->validate(
            $rules,
            $currentType?->isFileBased()
            ? FileUploader::messagesForPendingUploads('pendingDeliverableFiles', maxFiles: $this->deliverableMaxFiles)
            : [],
        );

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
            $deliverable = $this->deliverableService->updateDeliverable($deliverable, $data, Auth::user());
            $this->syncDeliverableFiles($deliverable);
            $this->notifySuccess(__('Deliverable updated.'));
        } else {
            $deliverable = $this->deliverableService->createDeliverable($data, Auth::user());
            $this->syncDeliverableFiles($deliverable);
            $this->notifySuccess(__('Deliverable created.'));
        }

        $this->reset('name', 'description', 'milestone_unique_id', 'due_date', 'link', 'content', 'uniqueId', 'pendingDeliverableFiles');
        $this->fileUploaderState = $this->emptyFileUploaderState();
        $this->type = DeliverableType::FILE->value;

        $this->dispatch('deliverable-created');

        $this->modal('save-deliverable')->close();
    }

    private function syncDeliverableFiles(Deliverable $deliverable): void
    {
        if (! $deliverable->type->isFileBased()) {
            return;
        }

        foreach ($this->fileUploaderState['removed_ids'] as $fileUniqueId) {
            $file = DeliverableFile::query()
                ->where('unique_id', $fileUniqueId)
                ->where('deliverable_unique_id', $deliverable->unique_id)
                ->first();

            if ($file === null) {
                continue;
            }

            $this->authorize('delete', $file);
            $this->deliverableFileService->deleteFile($file, Auth::user());
        }

        if ($this->fileUploaderState['pending'] === []) {
            return;
        }

        $this->authorize('uploadFile', $deliverable);
        $this->deliverableFileService->uploadFiles(
            $deliverable,
            $this->fileUploaderState['pending'],
            Auth::user(),
        );
    }

    private function loadExistingFiles(Deliverable $deliverable): void
    {
        $this->fileUploaderState = $this->emptyFileUploaderState();

        if (! $deliverable->type->isFileBased()) {
            return;
        }

        $this->fileUploaderState['existing'] = $this->deliverableFileService
            ->getActiveFiles($deliverable->unique_id)
            ->map(fn (DeliverableFile $file): array => [
                'id' => $file->unique_id,
                'label' => $file->original_filename,
                'size' => $file->file_size,
                'mime_type' => $file->mimeTypeValue(),
                'preview_url' => $file->showUrl($this->projectUniqueId),
                'download_url' => $file->showUrl($this->projectUniqueId, 'attachment'),
                'is_image' => $file->isPreviewableImage(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     existing: list<array>,
     *     pending: list<mixed>,
     *     removed_ids: list<string>
     * }
     */
    private function emptyFileUploaderState(): array
    {
        return [
            'existing' => [],
            'pending' => [],
            'removed_ids' => [],
        ];
    }

    public function render()
    {
        return view('livewire.agency.projects.deliverables.save-deliverable');
    }
}
