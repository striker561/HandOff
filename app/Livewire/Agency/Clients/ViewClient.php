<?php

namespace App\Livewire\Agency\Clients;

use App\Concerns\WithNotifications;
use App\Services\ClientService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewClient extends Component
{
    use WithNotifications;

    #[Locked]
    public ?string $uniqueId = null;

    #[Locked]
    public string $name = '';

    #[Locked]
    public string $email = '';

    #[Locked]
    public string $status = '';

    #[Locked]
    public string $joined = '';

    #[Locked]
    public bool $isInvited = false;

    private ClientService $clientService;

    public function boot(ClientService $clientService): void
    {
        $this->clientService = $clientService;
    }

    #[On('open-client-view')]
    public function open(string $uniqueId): void
    {
        $client = $this->clientService->findClient($uniqueId);

        if (! $client) {
            $this->notifyError(__('Client not found.'));

            return;
        }

        $this->authorize('view', $client);

        $this->uniqueId = $client->unique_id;
        $this->name = $client->name;
        $this->email = $client->email;
        $this->status = $client->email_verified_at ? __('Active') : __('Invited');
        $this->joined = $client->created_at->format('M j, Y');
        $this->isInvited = $client->email_verified_at === null;

        $this->resetErrorBag();

        $this->modal('view-client')->show();
    }

    public function resendInvitation(): void
    {
        if ($this->uniqueId === null) {
            return;
        }

        $client = $this->clientService->findClient($this->uniqueId);

        if (! $client) {
            $this->notifyError(__('Client not found.'));

            return;
        }

        $this->authorize('resendInvitation', $client);

        $this->clientService->resendInvitation($client, Auth::user());

        $this->notifySuccess(__('Invitation resent to :name.', ['name' => $client->name]));
    }

    public function close(): void
    {
        $this->uniqueId = null;
        $this->name = '';
        $this->email = '';
        $this->status = '';
        $this->joined = '';
        $this->isInvited = false;

        $this->resetErrorBag();

        $this->modal('view-client')->close();
    }

    public function render()
    {
        return view('livewire.agency.clients.view-client');
    }
}
