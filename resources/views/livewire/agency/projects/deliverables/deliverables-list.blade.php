<div>
    <x-agency.project-hub.section :heading="__('Deliverables')">
        <x-slot:actions>
            <x-ui.button wire:click="openCreateDeliverable" icon="plus" class="!w-auto">
                {{ __('Add deliverable') }}
            </x-ui.button>
        </x-slot:actions>

        @if ($this->deliverables->isEmpty())
            <div class="project-overview__empty">
                <flux:text>{{ __('Create deliverables and link them to milestones for the handoff pipeline.') }}
                </flux:text>
            </div>
        @else
            <x-ui.data-table :paginate="$this->deliverables" :panel="false">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column class="hidden md:table-cell">{{ __('Milestone') }}</flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">{{ __('Type') }}</flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Due date') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Version') }}</flux:table.column>
                    <flux:table.column class="handoff-data-table__action hidden w-32 sm:table-cell">
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

                            <x-ui.data-table.action-cell class="w-32">
                                @if ($deliverable->status !== \App\Enums\Deliverable\DeliverableStatus::APPROVED)
                                    <x-ui.button variant="outline" icon="pencil-square"
                                        wire:click="editDeliverable('{{ $deliverable->unique_id }}')"
                                        class="!w-auto px-3 py-2">
                                        <span class="sr-only">{{ __('Edit') }}</span>
                                    </x-ui.button>
                                    <x-ui.button variant="outline" icon="check"
                                        wire:click="approve('{{ $deliverable->unique_id }}')"
                                        class="!w-auto px-3 py-2">
                                        <span class="sr-only">{{ __('Approve') }}</span>
                                    </x-ui.button>
                                @endif
                                @if ($deliverable->status !== \App\Enums\Deliverable\DeliverableStatus::REJECTED)
                                    <x-ui.button variant="outline" icon="x-mark"
                                        wire:click="reject('{{ $deliverable->unique_id }}')" class="!w-auto px-3 py-2">
                                        <span class="sr-only">{{ __('Reject') }}</span>
                                    </x-ui.button>
                                @endif
                            </x-ui.data-table.action-cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </x-ui.data-table>
        @endif
    </x-agency.project-hub.section>
</div>
