<div>
    @if ($this->deliverables->isEmpty())
        <x-ui.empty-state icon="document-text" :heading="__('No deliverables yet')" :text="__('Create deliverables and link them to milestones for the handoff pipeline.')">
            <x-slot:actions>
                <flux:button wire:click="openCreateDeliverable" variant="primary" icon="plus">
                    {{ __('Add deliverable') }}
                </flux:button>
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <x-ui.page-header :heading="__('Deliverables')" :subheading="__('Work items produced through project milestones.')">
            <x-slot:actions>
                <flux:button wire:click="openCreateDeliverable" variant="primary" icon="plus">
                    {{ __('Add deliverable') }}
                </flux:button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.data-table :paginate="$this->deliverables" class="mt-6">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column class="hidden md:table-cell">{{ __('Milestone') }}</flux:table.column>
                <flux:table.column class="hidden sm:table-cell">{{ __('Type') }}</flux:table.column>
                <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">{{ __('Due date') }}</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">{{ __('Version') }}</flux:table.column>
                <flux:table.column class="handoff-data-table__action hidden w-24 sm:table-cell">
                    <span class="sr-only">{{ __('Actions') }}</span>
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->deliverables as $deliverable)
                    <flux:table.row :key="$deliverable->unique_id">
                        <x-ui.data-table.primary-cell :title="$deliverable->name" :meta="$deliverable->milestone?->name">
                            <x-slot:mobile>
                                <flux:badge :color="$deliverable->status->badgeColor()" size="sm">
                                    {{ $deliverable->status->label() }}
                                </flux:badge>
                            </x-slot:mobile>
                        </x-ui.data-table.primary-cell>

                        <flux:table.cell class="hidden md:table-cell">
                            {{ $deliverable->milestone?->name ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell class="hidden sm:table-cell">
                            {{ $deliverable->type->label() }}
                        </flux:table.cell>

                        <flux:table.cell class="hidden sm:table-cell">
                            <flux:badge :color="$deliverable->status->badgeColor()" size="sm">
                                {{ $deliverable->status->label() }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="hidden lg:table-cell">
                            {{ $deliverable->due_date?->format('M j, Y') ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell class="hidden lg:table-cell">
                            v{{ $deliverable->version }}
                        </flux:table.cell>

                        <x-ui.data-table.action-cell class="w-24">
                            @if ($deliverable->status !== \App\Enums\Deliverable\DeliverableStatus::APPROVED)
                                <flux:button wire:click="approve('{{ $deliverable->unique_id }}')" variant="ghost" size="sm"
                                    icon="check" :tooltip="__('Approve')" />
                            @endif
                            @if ($deliverable->status !== \App\Enums\Deliverable\DeliverableStatus::REJECTED)
                                <flux:button wire:click="reject('{{ $deliverable->unique_id }}')" variant="ghost" size="sm"
                                    icon="x-mark" :tooltip="__('Reject')" />
                            @endif
                        </x-ui.data-table.action-cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </x-ui.data-table>
    @endif
</div>