<?php

namespace App\Livewire\Agency\Projects\Credentials;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Enums\Credential\CredentialType;
use App\Models\Credential;
use App\Services\CredentialService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateCredential extends Component
{
    use WithActionRateLimiting, WithNotifications;

    #[Locked]
    public ?string $projectUniqueId = null;

    public string $name = '';

    public string $type = 'login';

    public string $username = '';

    public string $password = '';

    public string $url = '';

    public string $notes = '';

    private CredentialService $credentialService;

    public function boot(CredentialService $credentialService): void
    {
        $this->credentialService = $credentialService;
    }

    #[On('open-create-credential')]
    public function open(string $projectUniqueId): void
    {
        $this->projectUniqueId = $projectUniqueId;
        $this->reset('name', 'username', 'password', 'url', 'notes');
        $this->type = CredentialType::LOGIN->value;
        $this->resetValidation();
        $this->modal('create-credential')->show();
    }

    #[Computed]
    public function credentialTypes(): Collection
    {
        return collect(CredentialType::cases());
    }

    public function create(): void
    {
        $this->authorize('create', Credential::class);

        if ($this->projectUniqueId === null) {
            return;
        }

        if (! $this->attemptRateLimitedAction('create-credential', maxAttempts: 10, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'type' => ['required', Rule::enum(CredentialType::class)],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:1', 'max:1000'],
            'url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->credentialService->createCredential([
            'project_unique_id' => $this->projectUniqueId,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'username' => $validated['username'] ?: null,
            'password' => $validated['password'],
            'url' => $validated['url'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ], Auth::user());

        $this->reset('name', 'username', 'password', 'url', 'notes');
        $this->type = CredentialType::LOGIN->value;

        $this->modal('create-credential')->close();

        $this->notifySuccess(__('Credential saved.'));

        $this->dispatch('credential-created');
    }

    public function render()
    {
        return view('livewire.agency.projects.credentials.create-credential');
    }
}
