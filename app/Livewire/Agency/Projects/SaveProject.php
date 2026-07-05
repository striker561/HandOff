<?php

namespace App\Livewire\Agency\Projects;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Data\Projects\SaveProjectData;
use App\Enums\Project\ProjectCurrency;
use App\Models\Project;
use App\Models\User;
use App\Services\ClientService;
use App\Services\ProjectService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class SaveProject extends Component
{
    use WithActionRateLimiting, WithNotifications;

    #[Locked]
    public ?string $uniqueId = null;

    public string $client_unique_id = '';

    public string $clientSearch = '';

    public string $name = '';

    public string $description = '';

    public ?string $budget = null;

    public string $currency = 'usd';

    public ?string $start_date = null;

    public ?string $due_date = null;

    public ?string $color = null;

    private ClientService $clientService;

    private ProjectService $projectService;

    public function boot(ClientService $clientService, ProjectService $projectService): void
    {
        $this->clientService = $clientService;
        $this->projectService = $projectService;
    }

    #[Computed]
    public function isEditing(): bool
    {
        return $this->uniqueId !== null;
    }

    #[Computed]
    public function selectedClient(): ?User
    {
        if (blank($this->client_unique_id)) {
            return null;
        }

        return $this->clientService->findClient($this->client_unique_id);
    }

    #[Computed]
    public function clients(): Collection
    {
        $search = trim($this->clientSearch);

        if (strlen($search) < 2) {
            return collect();
        }

        return $this->clientService->searchClientsForSelect($search);
    }

    #[On('open-save-project')]
    public function open(?string $uniqueId = null): void
    {
        $this->uniqueId = $uniqueId;
        $this->reset(
            'client_unique_id',
            'clientSearch',
            'name',
            'description',
            'budget',
            'start_date',
            'due_date',
            'color',
        );
        $this->currency = ProjectCurrency::USD->value;
        $this->resetValidation();

        if ($uniqueId !== null) {
            $project = $this->findProject($uniqueId);

            if ($project === null) {
                $this->notifyError(__('Project not found.'));

                return;
            }

            $this->authorize('view', $project);

            $this->client_unique_id = $project->client_unique_id;
            $this->name = $project->name;
            $this->description = $project->description ?? '';
            $this->budget = $project->budget !== null ? (string) $project->budget : null;
            $this->currency = $project->currency->value;
            $this->start_date = $project->start_date?->format('Y-m-d');
            $this->due_date = $project->due_date?->format('Y-m-d');
            $this->color = $project->color;
        }

        $this->modal('save-project')->show();
    }

    public function selectClient(string $uniqueId): void
    {
        $client = $this->clients()->firstWhere('unique_id', $uniqueId)
            ?? $this->clientService->findClient($uniqueId);

        if ($client === null) {
            return;
        }

        $this->client_unique_id = $client->unique_id;
        $this->clientSearch = '';
    }

    public function clearClient(): void
    {
        $this->reset('client_unique_id', 'clientSearch');
    }

    public function updatedColor(?string $value): void
    {
        if ($value === '') {
            $this->color = null;
        }
    }

    public function save(): void
    {
        if ($this->isEditing()) {
            $project = $this->findProject($this->uniqueId);

            if ($project === null) {
                $this->notifyError(__('Project not found.'));

                return;
            }

            $this->authorize('update', $project);
        } else {
            $this->authorize('create', Project::class);
        }

        if (! $this->attemptRateLimitedAction('save-project', maxAttempts: 10, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $validated = $this->validate([
            'client_unique_id' => ['required', 'string', 'exists:users,unique_id'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'currency' => ['required', Rule::enum(ProjectCurrency::class)],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $data = SaveProjectData::fromArray($validated);

        if ($this->isEditing()) {
            $project = $this->findProject($this->uniqueId);
            $this->projectService->updateProject($project, $data, Auth::user());
            $this->notifySuccess(__('Project updated.'));
            $this->dispatch('project-updated');
        } else {
            $this->projectService->createProject($data, Auth::user());
            $this->notifySuccess(__('Project created.'));
            $this->dispatch('project-created');
        }

        $this->reset(
            'uniqueId',
            'client_unique_id',
            'clientSearch',
            'name',
            'description',
            'budget',
            'start_date',
            'due_date',
            'color',
        );
        $this->currency = ProjectCurrency::USD->value;

        $this->modal('save-project')->close();
    }

    private function findProject(string $uniqueId): ?Project
    {
        return $this->projectService->findProject($uniqueId);
    }

    public function render()
    {
        return view('livewire.agency.projects.save-project');
    }
}
