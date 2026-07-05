<?php

namespace App\Livewire\Agency\Projects\Credentials;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Data\Credentials\SaveCredentialData;
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

class SaveCredential extends Component
{
    use WithActionRateLimiting, WithNotifications;

    #[Locked]
    public ?string $projectUniqueId = null;

    #[Locked]
    public ?string $uniqueId = null;

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

    #[Computed]
    public function isEditing(): bool
    {
        return $this->uniqueId !== null;
    }

    #[On('open-save-credential')]
    public function open(string $projectUniqueId, ?string $uniqueId = null): void
    {
        $this->projectUniqueId = $projectUniqueId;
        $this->uniqueId = $uniqueId;
        $this->reset('name', 'username', 'password', 'url', 'notes');
        $this->type = CredentialType::LOGIN->value;
        $this->resetValidation();

        if ($uniqueId !== null) {
            $credential = $this->findCredential($uniqueId, $projectUniqueId);

            if ($credential === null) {
                $this->notifyError(__('Credential not found.'));

                return;
            }

            $this->authorize('update', $credential);

            $this->name = $credential->name;
            $this->type = $credential->type->value;
            $this->username = $credential->username ?? '';
            $this->url = $credential->url ?? '';
            $this->notes = $credential->notes ?? '';
        } else {
            $this->authorize('create', Credential::class);
        }

        $this->modal('save-credential')->show();
    }

    #[Computed]
    public function credentialTypes(): Collection
    {
        return collect(CredentialType::cases());
    }

    public function save(): void
    {
        if ($this->projectUniqueId === null) {
            return;
        }

        if ($this->isEditing()) {
            $credential = $this->findCredential($this->uniqueId, $this->projectUniqueId);

            if ($credential === null) {
                $this->notifyError(__('Credential not found.'));

                return;
            }

            $this->authorize('update', $credential);
        } else {
            $this->authorize('create', Credential::class);
        }

        if (! $this->attemptRateLimitedAction('save-credential', maxAttempts: 10, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $passwordRules = $this->isEditing()
            ? ['nullable', 'string', 'min:1', 'max:1000']
            : ['required', 'string', 'min:1', 'max:1000'];

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'type' => ['required', Rule::enum(CredentialType::class)],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => $passwordRules,
            'url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = SaveCredentialData::fromArray([
            'project_unique_id' => $this->projectUniqueId,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'username' => $validated['username'] ?? null,
            'password' => $validated['password'] ?? null,
            'url' => $validated['url'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($this->isEditing()) {
            $credential = $this->findCredential($this->uniqueId, $this->projectUniqueId);
            $this->credentialService->updateCredential($credential, $data, Auth::user());
            $this->notifySuccess(__('Credential updated.'));
            $this->dispatch('credential-updated');
        } else {
            $this->credentialService->createCredential($data, Auth::user());
            $this->notifySuccess(__('Credential saved.'));
            $this->dispatch('credential-created');
        }

        $this->reset('name', 'username', 'password', 'url', 'notes', 'uniqueId');
        $this->type = CredentialType::LOGIN->value;

        $this->modal('save-credential')->close();
    }

    private function findCredential(string $uniqueId, string $projectUniqueId): ?Credential
    {
        return Credential::query()
            ->where('unique_id', $uniqueId)
            ->where('project_unique_id', $projectUniqueId)
            ->first();
    }

    public function render()
    {
        return view('livewire.agency.projects.credentials.save-credential');
    }
}
