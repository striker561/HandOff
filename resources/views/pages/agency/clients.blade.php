<x-layouts::app :title="__('Clients')">
    <div class="handoff-page handoff-page--wide">
        <header class="handoff-page__header">
            <div class="space-y-1">
                <flux:heading size="xl">
                    {{ __('Clients') }}
                </flux:heading>
                <flux:text class="handoff-page__lede">
                    {{ __('Manage the clients in your agency workspace.') }}
                </flux:text>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <flux:input wire:model.live="search" placeholder="{{ __('Search clients…') }}" icon="magnifying-glass"
                    class="w-full sm:max-w-xs" />

                <flux:modal.trigger name="create-client" class="shrink-0">
                    <x-ui.button icon="user-plus" class="!w-auto">
                        {{ __('Create') }}
                    </x-ui.button>
                </flux:modal.trigger>
            </div>
        </header>

        <livewire:agency.clients.clients-list />
    </div>

    <livewire:agency.clients.create-client />
</x-layouts::app>
