<?php

namespace App\Livewire\Agency\Projects\Credentials;

use App\Services\CredentialService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CredentialsList extends Component
{
    use WithPagination;

    #[Locked]
    public string $projectUniqueId;

    private CredentialService $credentialService;

    public function boot(CredentialService $credentialService): void
    {
        $this->credentialService = $credentialService;
    }

    #[On('credential-created')]
    #[On('credential-updated')]
    public function refreshCredentials(): void
    {
        $this->resetPage();
    }

    public function openCreateCredential(): void
    {
        $this->dispatch('open-save-credential', projectUniqueId: $this->projectUniqueId)
            ->to(SaveCredential::class);
    }

    public function viewCredential(string $uniqueId): void
    {
        $this->dispatch('open-credential-view', uniqueId: $uniqueId, projectUniqueId: $this->projectUniqueId)
            ->to(ViewCredential::class);
    }

    public function editCredential(string $uniqueId): void
    {
        $this->dispatch('open-save-credential', projectUniqueId: $this->projectUniqueId, uniqueId: $uniqueId)
            ->to(SaveCredential::class);
    }

    #[Computed]
    public function credentials(): LengthAwarePaginator
    {
        return $this->credentialService->getCredentialsForProject($this->projectUniqueId, [
            'sort' => 'name',
            'direction' => 'asc',
            'per_page' => 50,
        ]);
    }

    public function render()
    {
        return view('livewire.agency.projects.credentials.credentials-list');
    }
}
