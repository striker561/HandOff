<flux:modal name="view-project" flyout variant="floating" class="md:w-lg" @close="close">
    @if ($uniqueId)
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <flux:heading size="lg">{{ $name }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Project overview and upcoming work.') }}</flux:text>
                </div>

                <flux:badge :color="$statusBadgeColor" size="sm" class="shrink-0">
                    {{ $statusLabel }}
                </flux:badge>
            </div>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:text class="text-sm font-medium">{{ __('Client') }}</flux:text>
                    <flux:text class="mt-1">{{ $clientName }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm font-medium">{{ __('Budget') }}</flux:text>
                    <flux:text class="mt-1">
                        @if ($formattedBudget)
                            {{ $formattedBudget }}
                        @else
                            <x-ui.data-table.empty />
                        @endif
                    </flux:text>
                </div>
                <div>
                    <flux:text class="text-sm font-medium">{{ __('Start date') }}</flux:text>
                    <flux:text class="mt-1">{{ $formattedStartDate ?? __('No date') }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm font-medium">{{ __('Due date') }}</flux:text>
                    <flux:text class="mt-1">{{ $formattedDueDate ?? __('No date') }}</flux:text>
                </div>
                <div class="sm:col-span-2">
                    <flux:text class="text-sm font-medium">{{ __('Progress') }}</flux:text>
                    <flux:text class="mt-1">{{ $progressPercentage }}%</flux:text>
                </div>
                @if ($description)
                    <div class="sm:col-span-2">
                        <flux:text class="text-sm font-medium">{{ __('Description') }}</flux:text>
                        <flux:text class="mt-1">{{ $description }}</flux:text>
                    </div>
                @endif
            </dl>

            <flux:separator />

            <section class="space-y-3">
                <flux:heading size="sm">{{ __('Milestones') }}</flux:heading>
                <flux:text variant="subtle">{{ __('Milestone tracking will appear here.') }}</flux:text>
            </section>

            <section class="space-y-3">
                <flux:heading size="sm">{{ __('Deliverables') }}</flux:heading>
                <flux:text variant="subtle">{{ __('Deliverables linked to this project will appear here.') }}</flux:text>
            </section>

            <x-ui.modal-footer>
                <flux:button wire:click="close" variant="filled">{{ __('Close') }}</flux:button>
                <flux:button variant="primary" icon="plus" disabled>
                    {{ __('Add milestone') }}
                </flux:button>
            </x-ui.modal-footer>
        </div>
    @endif
</flux:modal>