<?php

namespace App\Livewire\Agency\Projects;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Data\Projects\CreateProjectData;
use App\Enums\Project\ProjectCurrency;
use App\Models\Project;
use App\Models\User;
use App\Services\ClientService;
use App\Services\ProjectService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateProject extends Component
{
    use WithActionRateLimiting, WithNotifications;

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

    public function selectClient(string $uniqueId): void
    {
        $client = $this->clients->firstWhere('unique_id', $uniqueId)
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

    public function create(): void
    {
        $this->authorize('create', Project::class);

        if (! $this->attemptRateLimitedAction('create-project', maxAttempts: 10, decaySeconds: 60)) {
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

        $this->projectService->createProject(
            CreateProjectData::fromArray($validated),
            Auth::user(),
        );

        $this->reset(
            'client_unique_id',
            'clientSearch',
            'name',
            'description',
            'budget',
            'currency',
            'start_date',
            'due_date',
            'color',
        );

        $this->currency = ProjectCurrency::USD->value;

        $this->modal('create-project')->close();

        $this->notifySuccess(__('Project created.'));

        $this->dispatch('project-created');
    }

    public function render()
    {
        return view('livewire.agency.projects.create-project');
    }
}
