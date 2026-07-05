<?php

namespace App\Livewire\Agency\Clients;

use App\Models\User;
use App\Services\ClientService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Clients')]
class ClientsList extends Component
{
    use WithPagination;

    #[Url(as: 'search', history: true)]
    public string $search = '';

    #[Url(as: 'sort', history: true)]
    public string $sort = 'created_at';

    #[Url(as: 'direction', history: true)]
    public string $direction = 'desc';

    private ClientService $clientService;

    public function boot(ClientService $clientService): void
    {
        $this->clientService = $clientService;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    #[On('client-created')]
    public function refreshClients(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->direction = 'asc';
        }
    }

    public function openSaveClient(): void
    {
        $this->dispatch('open-save-client')->to(SaveClient::class);
    }

    public function viewClient(string $uniqueId): void
    {
        $this->dispatch('open-client-view', uniqueId: $uniqueId)->to(ViewClient::class);
    }

    #[Computed]
    public function clients(): LengthAwarePaginator
    {
        return $this->clientService->getClients([
            'search' => $this->search,
            'sort' => $this->sort,
            'direction' => $this->direction,
        ]);
    }

    public function render()
    {
        return view('livewire.agency.clients.clients-list');
    }
}
