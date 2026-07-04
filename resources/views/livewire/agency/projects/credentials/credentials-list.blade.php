<div>
    @if ($this->credentials->isEmpty())
        <x-ui.empty-state icon="key" :heading="__('No credentials yet')" :text="__('Store shared logins and access details for this project.')">
            <x-slot:actions>
                <flux:button wire:click="openCreateCredential" variant="primary" icon="plus">
                    {{ __('Add credential') }}
                </flux:button>
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <x-ui.page-header :heading="__('Credentials')" :subheading="__('Shared credential vault for this project.')">
            <x-slot:actions>
                <flux:button wire:click="openCreateCredential" variant="primary" icon="plus">
                    {{ __('Add credential') }}
                </flux:button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.data-table :paginate="$this->credentials" class="mt-6">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column class="hidden sm:table-cell">{{ __('Type') }}</flux:table.column>
                <flux:table.column class="hidden md:table-cell">{{ __('Username') }}</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">{{ __('URL') }}</flux:table.column>
                <flux:table.column class="handoff-data-table__action hidden w-24 sm:table-cell">
                    <span class="sr-only">{{ __('Actions') }}</span>
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->credentials as $credential)
                    <flux:table.row :key="$credential->unique_id">
                        <x-ui.data-table.primary-cell :title="$credential->name" :meta="$credential->username">
                            <x-slot:mobile>
                                <flux:badge :color="$credential->type->badgeColor()" size="sm">
                                    {{ $credential->type->label() }}
                                </flux:badge>
                            </x-slot:mobile>
                            <x-slot:action>
                                <flux:button wire:click="viewCredential('{{ $credential->unique_id }}')" variant="ghost"
                                    size="sm" icon="eye" :tooltip="__('View')" />
                            </x-slot:action>
                        </x-ui.data-table.primary-cell>

                        <flux:table.cell class="hidden sm:table-cell">
                            <flux:badge :color="$credential->type->badgeColor()" size="sm">
                                {{ $credential->type->label() }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="hidden md:table-cell">
                            {{ $credential->username ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell class="hidden lg:table-cell">
                            @if ($credential->url)
                                <a href="{{ $credential->url }}" target="_blank" rel="noopener noreferrer"
                                    class="truncate text-brand-600 hover:underline dark:text-brand-400">
                                    {{ $credential->url }}
                                </a>
                            @else
                                —
                            @endif
                        </flux:table.cell>

                        <x-ui.data-table.action-cell class="w-24">
                            <flux:button wire:click="viewCredential('{{ $credential->unique_id }}')" variant="ghost" size="sm"
                                icon="eye" :tooltip="__('View')" />
                            <flux:button wire:click="editCredential('{{ $credential->unique_id }}')" variant="ghost" size="sm"
                                icon="pencil-square" :tooltip="__('Edit')" />
                        </x-ui.data-table.action-cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </x-ui.data-table>
    @endif
</div>