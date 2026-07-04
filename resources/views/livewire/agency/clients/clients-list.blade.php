<div>
    @if (count($this->clients) > 0)
        <flux:table>
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sort === 'name'" :direction="$sort === 'name' ? $direction : null"
                    wire:click="sortBy('name')">
                    {{ __('Name') }}
                </flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'email'" :direction="$sort === 'email' ? $direction : null"
                    wire:click="sortBy('email')">
                    {{ __('Email') }}
                </flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'created_at'"
                    :direction="$sort === 'created_at' ? $direction : null" wire:click="sortBy('created_at')">
                    {{ __('Joined') }}
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->clients as $client)
                    <flux:table.row :key="$client->unique_id">
                        <flux:table.cell class="font-medium">{{ $client->name }}</flux:table.cell>
                        <flux:table.cell>{{ $client->email }}</flux:table.cell>
                        <flux:table.cell>{{ $client->created_at->format('M j, Y') }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        <div class="mt-6">
            {{ $this->clients->links() }}
        </div>
    @else
        <x-ui.empty-state icon="users" :heading="$search ? __('No clients match your search') : __('No clients yet')"
            :text="$search ? __('Try adjusting your search terms or clear the search field.') : __('Create your first client to get started with project collaboration and deliverables.')">
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