<div>
    <x-agency.project-hub.section :heading="__('Credentials')" flush>
        <x-slot:description>
            {{ __('Shared logins and access your client needs after handoff.') }}
        </x-slot:description>
        <x-slot:actions>
            <x-ui.button wire:click="openSaveCredential" icon="plus" class="sm:!w-auto">
                {{ __('Add credential') }}
            </x-ui.button>
        </x-slot:actions>

        @if ($this->credentials->isEmpty())
            <x-ui.empty-state compact icon="key" :heading="__('No credentials yet')" :text="__('Store production logins, API keys, and other access details in one secure place. Clients reveal passwords only when they need them.')">
                <x-slot:actions>
                    <x-ui.button wire:click="openSaveCredential" icon="plus" class="sm:!w-auto">
                        {{ __('Add credential') }}
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        @else
            <x-ui.data-table :paginate="$this->credentials" :panel="false" flush>
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">{{ __('Type') }}</flux:table.column>
                    <flux:table.column class="hidden md:table-cell">{{ __('Username') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('URL') }}</flux:table.column>
                    <flux:table.column class="handoff-data-table__action hidden sm:table-cell">
                        <span class="sr-only">{{ __('Actions') }}</span>
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->credentials as $credential)
                        <flux:table.row :key="$credential->unique_id">
                            <x-ui.data-table.primary-cell :title="$credential->name" :meta="$credential->username ?? ($credential->url ? parse_url($credential->url, PHP_URL_HOST) : __('No username'))"
                                wire-click="viewCredential('{{ $credential->unique_id }}')">
                                <x-slot:mobile>
                                    <flux:badge :color="$credential->type->badgeColor()" size="sm">
                                        {{ $credential->type->label() }}
                                    </flux:badge>
                                </x-slot:mobile>
                                <x-slot:action>
                                    <x-ui.button icon="pencil-square"
                                        wire:click="editCredential('{{ $credential->unique_id }}')">
                                        {{ __('Edit credential') }}
                                    </x-ui.button>
                                </x-slot:action>
                            </x-ui.data-table.primary-cell>

                            <flux:table.cell class="hidden sm:table-cell">
                                <flux:badge :color="$credential->type->badgeColor()" size="sm">
                                    {{ $credential->type->label() }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="hidden md:table-cell">
                                {{ $credential->username ?? __('No username') }}
                            </flux:table.cell>

                            <flux:table.cell class="hidden lg:table-cell">
                                @if ($credential->url)
                                    <a href="{{ $credential->url }}" target="_blank" rel="noopener noreferrer"
                                        class="truncate text-brand-600 hover:underline dark:text-brand-400">
                                        {{ $credential->url }}
                                    </a>
                                @else
                                    <span class="text-sm text-zinc-400 dark:text-zinc-500">{{ __('No link') }}</span>
                                @endif
                            </flux:table.cell>

                            <x-ui.data-table.action-cell>
                                <x-ui.button icon="pencil-square" icon-only
                                    wire:click="editCredential('{{ $credential->unique_id }}')">
                                    {{ __('Edit') }}
                                </x-ui.button>
                            </x-ui.data-table.action-cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </x-ui.data-table>
        @endif
    </x-agency.project-hub.section>
</div>