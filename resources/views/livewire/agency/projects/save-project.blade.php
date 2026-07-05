<flux:modal name="save-project" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $this->isEditing ? __('Edit Project') : __('Create Project') }}
            </flux:heading>
            <flux:text class="mt-2">
                {{ $this->isEditing
    ? __('Update project details for this client.')
    : __('Set up a new project for a client. You can add milestones and deliverables later.') }}
            </flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Client') }}</flux:label>

            @if ($this->selectedClient)
                <flux:input.group>
                    <flux:input readonly :value="$this->selectedClient->name . ' · ' . $this->selectedClient->email" />
                    <flux:input.group.suffix>
                        <flux:button type="button" wire:click="clearClient" variant="ghost" size="sm" icon="x-mark"
                            :tooltip="__('Change client')" />
                    </flux:input.group.suffix>
                </flux:input.group>
            @else
                <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="clientSearch"
                    placeholder="{{ __('Search by name or email…') }}" autocomplete="off" />

                @if (strlen(trim($clientSearch)) >= 2)
                    <div
                        class="mt-1 max-h-48 overflow-y-auto rounded-lg border border-zinc-200 bg-white p-0.5 shadow-xs dark:border-zinc-600 dark:bg-zinc-700">
                        <div wire:loading.flex wire:target="clientSearch"
                            class="items-center gap-2 px-2 py-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                            <flux:icon.loading variant="mini" />
                            {{ __('Searching…') }}
                        </div>

                        <div wire:loading.remove wire:target="clientSearch">
                            @forelse ($this->clients as $client)
                                <button type="button" wire:click="selectClient('{{ $client->unique_id }}')"
                                    wire:key="client-{{ $client->unique_id }}"
                                    class="flex w-full rounded-md px-2 py-1.5 text-start text-sm font-medium text-zinc-800 hover:bg-zinc-50 dark:text-white dark:hover:bg-zinc-600">
                                    {{ $client->name }} · {{ $client->email }}
                                </button>
                            @empty
                                <div class="px-2 py-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('No clients found.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                @elseif (strlen(trim($clientSearch)) === 1)
                    <flux:description class="mt-1">
                        {{ __('Type at least 2 characters to search.') }}
                    </flux:description>
                @endif
            @endif

            <flux:error name="client_unique_id" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Project name') }}</flux:label>
            <flux:input wire:model="name" placeholder="{{ __('e.g. Website Redesign') }}" wire:keydown.enter="save" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Description') }}</flux:label>
            <flux:textarea wire:model="description" placeholder="{{ __('Brief description of the project…') }}"
                rows="3" />
            <flux:error name="description" />
        </flux:field>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>{{ __('Budget') }}</flux:label>
                <flux:input type="number" step="0.01" min="0" wire:model="budget" placeholder="{{ __('0.00') }}" />
                <flux:error name="budget" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Currency') }}</flux:label>
                <flux:select wire:model="currency">
                    @foreach (\App\Enums\Project\ProjectCurrency::cases() as $currency)
                        <flux:select.option value="{{ $currency->value }}">{{ $currency->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="currency" />
            </flux:field>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>{{ __('Start date') }}</flux:label>
                <flux:input type="date" wire:model="start_date" />
                <flux:error name="start_date" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Due date') }}</flux:label>
                <flux:input type="date" wire:model="due_date" />
                <flux:error name="due_date" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>{{ __('Accent color') }}</flux:label>
            <flux:input.group>
                <flux:input.group.prefix>
                    <input type="color" wire:model.live="color"
                        class="size-9 cursor-pointer border-0 bg-transparent p-1" />
                </flux:input.group.prefix>
                <flux:input wire:model="color" placeholder="#3b82f6" />
                @if ($color)
                    <flux:input.group.suffix>
                        <flux:button type="button" wire:click="$set('color', null)" variant="ghost" size="sm" icon="x-mark"
                            :tooltip="__('Clear color')" />
                    </flux:input.group.suffix>
                @endif
            </flux:input.group>
            <flux:description>{{ __('Leave empty for the default accent.') }}</flux:description>
            <flux:error name="color" />
        </flux:field>

        <x-ui.modal-footer>
            <flux:modal.close>
                <x-ui.button variant="secondary" class="!w-auto">{{ __('Cancel') }}</x-ui.button>
            </flux:modal.close>
            <x-ui.button wire:click="save" class="!w-auto">
                {{ $this->isEditing ? __('Save changes') : __('Create Project') }}
            </x-ui.button>
        </x-ui.modal-footer>
    </div>
</flux:modal>