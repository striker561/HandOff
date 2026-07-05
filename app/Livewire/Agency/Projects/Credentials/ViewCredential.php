<?php

namespace App\Livewire\Agency\Projects\Credentials;

use App\Concerns\AuthorizesProjectHubResources;
use App\Concerns\WithNotifications;
use App\Models\Credential;
use App\Services\CredentialService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewCredential extends Component
{
    use AuthorizesProjectHubResources, WithNotifications;

    #[Locked]
    public ?string $uniqueId = null;

    #[Locked]
    public ?string $projectUniqueId = null;

    #[Locked]
    public string $name = '';

    #[Locked]
    public string $typeLabel = '';

    #[Locked]
    public string $typeBadgeColor = 'gray';

    #[Locked]
    public ?string $username = null;

    #[Locked]
    public ?string $url = null;

    #[Locked]
    public ?string $notes = null;

    public bool $passwordRevealed = false;

    #[Locked]
    public ?string $revealedPassword = null;

    private CredentialService $credentialService;

    public function boot(CredentialService $credentialService): void
    {
        $this->credentialService = $credentialService;
    }

    #[On('open-credential-view')]
    public function open(string $uniqueId, string $projectUniqueId): void
    {
        $credential = $this->viewHubResource(
            $uniqueId,
            $projectUniqueId,
            $this->credentialService->findCredentialForProject(...),
        );

        if (! $credential instanceof Credential) {
            $this->notifyError(__('Credential not found.'));

            return;
        }

        $this->uniqueId = $credential->unique_id;
        $this->projectUniqueId = $projectUniqueId;
        $this->name = $credential->name;
        $this->typeLabel = $credential->type->label();
        $this->typeBadgeColor = $credential->type->badgeColor();
        $this->username = $credential->username;
        $this->url = $credential->url;
        $this->notes = $credential->notes;
        $this->passwordRevealed = false;
        $this->revealedPassword = null;

        $this->modal('view-credential')->show();
    }

    public function revealPassword(): void
    {
        if ($this->uniqueId === null || $this->projectUniqueId === null) {
            return;
        }

        $credential = $this->authorizeHubResource(
            'reveal',
            $this->uniqueId,
            $this->projectUniqueId,
            $this->credentialService->findCredentialForProject(...),
        );

        if (! $credential instanceof Credential) {
            $this->notifyError(__('Credential not found.'));

            return;
        }

        $data = $this->credentialService->revealCredential($credential, Auth::user());

        $this->passwordRevealed = true;
        $this->revealedPassword = $data['password'];
    }

    public function edit(): void
    {
        if ($this->uniqueId === null || $this->projectUniqueId === null) {
            return;
        }

        $this->close();

        $this->dispatch('open-save-credential', projectUniqueId: $this->projectUniqueId, uniqueId: $this->uniqueId)
            ->to(SaveCredential::class);
    }

    public function close(): void
    {
        $this->uniqueId = null;
        $this->projectUniqueId = null;
        $this->name = '';
        $this->typeLabel = '';
        $this->typeBadgeColor = 'gray';
        $this->username = null;
        $this->url = null;
        $this->notes = null;
        $this->passwordRevealed = false;
        $this->revealedPassword = null;

        $this->modal('view-credential')->close();
    }

    public function render()
    {
        return view('livewire.agency.projects.credentials.view-credential');
    }
}
