<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;
use Laravel\Passkeys\Actions\DeletePasskey;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Passkey settings')]
class Passkeys extends Component
{
    #[Locked]
    public array $passkeys = [];

    #[Locked]
    public ?int $deletingPasskeyId = null;

    #[Locked]
    public string $deletingPasskeyName = '';

    public function mount(): void
    {
        if (! Features::canManagePasskeys()) {
            $this->redirect(route('security.edit'), navigate: true);

            return;
        }

        $this->loadPasskeys();
    }

    public function loadPasskeys(): void
    {
        $this->passkeys = Auth::user()->passkeys()
            ->select(['id', 'name', 'credential', 'created_at', 'last_used_at'])
            ->latest()
            ->get()
            ->map(fn ($passkey) => [
                'id' => $passkey->id,
                'name' => $passkey->name,
                'authenticator' => $passkey->authenticator,
                'created_at_diff' => $passkey->created_at->diffForHumans(),
                'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
            ])
            ->toArray();
    }

    public function confirmDelete(int $passkeyId): void
    {
        $passkey = Auth::user()->passkeys()->findOrFail($passkeyId);
        $this->deletingPasskeyId = $passkey->id;
        $this->deletingPasskeyName = $passkey->name;
        $this->modal('delete-passkey-modal')->show();
    }

    public function deletePasskey(DeletePasskey $deletePasskey): void
    {
        if (! $this->deletingPasskeyId) {
            return;
        }

        $passkey = Auth::user()->passkeys()->findOrFail($this->deletingPasskeyId);
        $deletePasskey(Auth::user(), $passkey);
        $this->closeDeleteModal();
        $this->loadPasskeys();
    }

    public function closeDeleteModal(): void
    {
        $this->deletingPasskeyId = null;
        $this->deletingPasskeyName = '';
        $this->modal('delete-passkey-modal')->close();
    }

    public function render()
    {
        return view('livewire.settings.passkeys');
    }
}
