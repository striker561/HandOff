<div>
    <x-agency.project-hub.section :heading="__('Milestones')">
        <x-slot:actions>
            <x-ui.button wire:click="openCreateMilestone" icon="plus" class="!w-auto">
                {{ __('Add milestone') }}
            </x-ui.button>
        </x-slot:actions>

        @if ($this->milestones->isEmpty())
            <div class="project-overview__empty">
                <flux:text>{{ __('Add phases to structure the handoff pipeline for this project.') }}</flux:text>
            </div>
        @else
            <x-ui.data-table :paginate="$this->milestones" :panel="false">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
                    <flux:table.column class="hidden md:table-cell">{{ __('Due date') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Deliverables') }}</flux:table.column>
                    <flux:table.column class="handoff-data-table__action hidden w-24 sm:table-cell">
                        <span class="sr-only">{{ __('Actions') }}</span>
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->milestones as $milestone)
                        <flux:table.row :key="$milestone->unique_id">
                            <x-ui.data-table.primary-cell :title="$milestone->name" :meta="$milestone->description">
                                <x-slot:mobile>
                                    <flux:badge :color="$milestone->status->badgeColor()" size="sm">
                                        {{ $milestone->status->label() }}
                                    </flux:badge>
                                </x-slot:mobile>
                                <x-slot:action>
                                    <x-ui.button variant="outline" icon="eye" :href="route('agency.projects.deliverables', [
                                        'projectUniqueId' => $projectUniqueId,
                                        'milestone' => $milestone->unique_id,
                                    ])" wire:navigate
                                        class="!w-auto px-3 py-2">
                                        <span class="sr-only">{{ __('View deliverables') }}</span>
                                    </x-ui.button>
                                </x-slot:action>
                            </x-ui.data-table.primary-cell>

                            <flux:table.cell class="hidden sm:table-cell">
                                <flux:badge :color="$milestone->status->badgeColor()" size="sm">
                                    {{ $milestone->status->label() }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="hidden md:table-cell">
                                {{ $milestone->due_date?->format('M j, Y') ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell class="hidden lg:table-cell">
                                {{ $milestone->deliverables_count }}
                            </flux:table.cell>

                            <x-ui.data-table.action-cell class="w-24">
                                <x-ui.button variant="outline" icon="pencil-square"
                                    wire:click="editMilestone('{{ $milestone->unique_id }}')"
                                    class="!w-auto px-3 py-2">
                                    <span class="sr-only">{{ __('Edit') }}</span>
                                </x-ui.button>
                                <x-ui.button variant="outline" icon="eye" :href="route('agency.projects.deliverables', [
                                    'projectUniqueId' => $projectUniqueId,
                                    'milestone' => $milestone->unique_id,
                                ])" wire:navigate
                                    class="!w-auto px-3 py-2">
                                    <span class="sr-only">{{ __('View deliverables') }}</span>
                                </x-ui.button>
                            </x-ui.data-table.action-cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </x-ui.data-table>
        @endif
    </x-agency.project-hub.section>
</div>
