<?php

namespace App\Livewire\Agency\Clients;

use App\Concerns\WithActionRateLimiting;
use App\Concerns\WithNotifications;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateClient extends Component
{
    use WithActionRateLimiting, WithNotifications;

    public string $name = '';

    public string $email = '';

    private ClientService $clientService;

    public function boot(ClientService $clientService): void
    {
        $this->clientService = $clientService;
    }

    public function create(): void
    {
        $this->authorize('create', User::class);

        if (! $this->attemptRateLimitedAction('create-client', maxAttempts: 3, decaySeconds: 60)) {
            $this->notifyWarning(__('Too many attempts. Please try again in a minute.'), duration: 8000);

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:190', 'unique:users,email'],
        ]);

        try {
            $this->clientService->createClient($validated, Auth::user());
        } catch (\Exception $e) {
            $this->notifyError($e->getMessage(), duration: 8000);

            return;
        }

        $this->reset('name', 'email');

        $this->modal('create-client')->close();

        $this->notifySuccess(__('Client invited. They will receive an email to set up their account.'));

        $this->dispatch('client-created');
    }

    public function render()
    {
        return view('livewire.agency.clients.create-client');
    }
}
