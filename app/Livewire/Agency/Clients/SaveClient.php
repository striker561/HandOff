<?php

namespace App\Livewire\Agency\Clients;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Data\Clients\SaveClientData;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class SaveClient extends Component
{
    use WithActionRateLimiting, WithNotifications;

    public string $name = '';

    public string $email = '';

    private ClientService $clientService;

    public function boot(ClientService $clientService): void
    {
        $this->clientService = $clientService;
    }

    #[On('open-save-client')]
    public function open(): void
    {
        $this->reset('name', 'email');
        $this->resetValidation();
        $this->authorize('create', User::class);
        $this->modal('save-client')->show();
    }

    public function save(): void
    {
        $this->authorize('create', User::class);

        if (!$this->attemptRateLimitedAction('save-client', maxAttempts: 3, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:190', 'unique:users,email'],
        ]);

        $this->clientService->createClient(
            SaveClientData::fromArray($validated),
            Auth::user(),
        );

        $this->reset('name', 'email');
        $this->modal('save-client')->close();
        $this->notifySuccess(__('Client invited. They will receive an email to set up their account.'));
        $this->dispatch('client-created');
    }

    public function render()
    {
        return view('livewire.agency.clients.save-client');
    }
}
