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

class EditCredential extends Component
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

    #[On('open-edit-credential')]
    public function open(string $uniqueId, string $projectUniqueId): void
    {
        $credential = $this->findCredential($uniqueId, $projectUniqueId);

        if ($credential === null) {
            $this->notifyError(__('Credential not found.'));

            return;
        }

        $this->authorize('update', $credential);

        $this->uniqueId = $credential->unique_id;
        $this->projectUniqueId = $projectUniqueId;
        $this->name = $credential->name;
        $this->type = $credential->type->value;
        $this->username = $credential->username ?? '';
        $this->password = '';
        $this->url = $credential->url ?? '';
        $this->notes = $credential->notes ?? '';
        $this->resetValidation();
        $this->modal('edit-credential')->show();
    }

    #[Computed]
    public function credentialTypes(): Collection
    {
        return collect(CredentialType::cases());
    }

    public function save(): void
    {
        if ($this->uniqueId === null || $this->projectUniqueId === null) {
            return;
        }

        $credential = $this->findCredential($this->uniqueId, $this->projectUniqueId);

        if ($credential === null) {
            $this->notifyError(__('Credential not found.'));

            return;
        }

        $this->authorize('update', $credential);

        if (! $this->attemptRateLimitedAction('edit-credential', maxAttempts: 10, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'type' => ['required', Rule::enum(CredentialType::class)],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:1', 'max:1000'],
            'url' => ['nullable', 'url', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'username' => $validated['username'] ?: null,
            'url' => $validated['url'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ];

        if ($validated['password'] !== '') {
            $data['password'] = $validated['password'];
        }

        $this->credentialService->updateCredential($credential, $data, Auth::user());

        $this->modal('edit-credential')->close();

        $this->notifySuccess(__('Credential updated.'));

        $this->dispatch('credential-updated');
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
        return view('livewire.agency.projects.credentials.edit-credential');
    }
}
