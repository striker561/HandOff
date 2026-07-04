<div>
    <x-agency.project-hub.section :heading="__('Credentials')">
        <x-slot:actions>
            <x-ui.button wire:click="openCreateCredential" icon="plus" class="!w-auto">
                {{ __('Add credential') }}
            </x-ui.button>
        </x-slot:actions>

        @if ($this->credentials->isEmpty())
            <div class="project-overview__empty">
                <flux:text>{{ __('Store shared logins and access details for this project.') }}</flux:text>
            </div>
        @else
            <x-ui.data-table :paginate="$this->credentials" :panel="false">
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
                                    <x-ui.data-table.view-button
                                        wire-click="viewCredential('{{ $credential->unique_id }}')" :name="$credential->name" />
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
                                        class="text-brand-600 dark:text-brand-400 truncate hover:underline">
                                        {{ $credential->url }}
                                    </a>
                                @else
                                    —
                                @endif
                            </flux:table.cell>

                            <x-ui.data-table.action-cell class="w-24">
                                <x-ui.data-table.view-button
                                    wire-click="viewCredential('{{ $credential->unique_id }}')" :name="$credential->name" />
                                <x-ui.button variant="outline" icon="pencil-square"
                                    wire:click="editCredential('{{ $credential->unique_id }}')"
                                    class="!w-auto px-3 py-2">
                                    <span class="sr-only">{{ __('Edit') }}</span>
                                </x-ui.button>
                            </x-ui.data-table.action-cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </x-ui.data-table>
        @endif
    </x-agency.project-hub.section>
</div>
