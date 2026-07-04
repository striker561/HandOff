<div>
    <x-ui.page-header
        :heading="__('Clients')"
        :subheading="__('Manage the clients in your agency workspace.')"
    >
        <x-slot:actions>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search clients…') }}"
                icon="magnifying-glass" class="w-full sm:max-w-xs" />

            <flux:modal.trigger name="create-client" class="shrink-0">
                <x-ui.button icon="user-plus" class="!w-auto">
                    {{ __('Create') }}
                </x-ui.button>
            </flux:modal.trigger>
        </x-slot:actions>
    </x-ui.page-header>

    @if (count($this->clients) > 0)
        <div class="overflow-x-auto my-5">
            <flux:table :paginate="$this->clients">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sort === 'name'" :direction="$sort === 'name' ? $direction : null"
                        wire:click="sortBy('name')">
                        {{ __('Name') }}
                    </flux:table.column>
                    <flux:table.column sortable :sorted="$sort === 'email'"
                        :direction="$sort === 'email' ? $direction : null" wire:click="sortBy('email')"
                        class="hidden md:table-cell">
                        {{ __('Email') }}
                    </flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sort === 'created_at'"
                        :direction="$sort === 'created_at' ? $direction : null" wire:click="sortBy('created_at')"
                        class="hidden lg:table-cell">
                        {{ __('Joined') }}
                    </flux:table.column>
                    <flux:table.column class="w-16">{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->clients as $client)
                        <flux:table.row :key="$client->unique_id">
                            <flux:table.cell variant="strong">
                                <div class="min-w-0">
                                    <div class="truncate">{{ $client->name }}</div>
                                    <div class="truncate text-sm text-zinc-500 md:hidden">{{ $client->email }}</div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="hidden md:table-cell">{{ $client->email }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($client->email_verified_at)
                                    <flux:badge color="lime" size="sm">{{ __('Active') }}</flux:badge>
                                @else
                                    <flux:badge color="amber" size="sm">{{ __('Invited') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="hidden lg:table-cell">
                                {{ $client->created_at->format('M j, Y') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <x-ui.button icon="eye" wire:click="viewClient('{{ $client->unique_id }}')"
                                    class="px-3" aria-label="{{ __('View :name', ['name' => $client->name]) }}">
                                    <span class="sr-only">{{ __('View details') }}</span>
                                </x-ui.button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @else
        <x-ui.empty-state icon="users" :heading="$search ? __('No clients match your search') : __('No clients yet')"
            :text="$search
                ? __('Try adjusting your search terms or clear the search field.')
                : __('Create your first client to get started with project collaboration and deliverables.')">
            <x-slot:actions>
                <flux:modal.trigger name="create-client">
                    <x-ui.button icon="user-plus">
                        {{ __('Create Client') }}
                    </x-ui.button>
                </flux:modal.trigger>
            </x-slot:actions>
        </x-ui.empty-state>
    @endif
</div>
