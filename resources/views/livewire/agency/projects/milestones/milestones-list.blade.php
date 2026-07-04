<div>
    @if ($this->milestones->isEmpty())
        <x-ui.empty-state icon="flag" :heading="__('No milestones yet')" :text="__('Add phases to structure the handoff pipeline for this project.')">
            <x-slot:actions>
                <flux:button wire:click="openCreateMilestone" variant="primary" icon="plus">
                    {{ __('Add milestone') }}
                </flux:button>
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <x-ui.page-header :heading="__('Milestones')" :subheading="__('Project phases that produce deliverables.')">
            <x-slot:actions>
                <flux:button wire:click="openCreateMilestone" variant="primary" icon="plus">
                    {{ __('Add milestone') }}
                </flux:button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.data-table :paginate="$this->milestones" class="mt-6">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
                <flux:table.column class="hidden md:table-cell">{{ __('Due date') }}</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">{{ __('Deliverables') }}</flux:table.column>
                <flux:table.column class="handoff-data-table__action hidden w-12 sm:table-cell sm:w-16">
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
                                <flux:button
                                    href="{{ route('agency.projects.deliverables', ['projectUniqueId' => $projectUniqueId, 'milestone' => $milestone->unique_id]) }}"
                                    wire:navigate variant="ghost" size="sm" icon="eye" :tooltip="__('View deliverables')" />
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

                        <x-ui.data-table.action-cell>
                            <flux:button
                                href="{{ route('agency.projects.deliverables', ['projectUniqueId' => $projectUniqueId, 'milestone' => $milestone->unique_id]) }}"
                                wire:navigate variant="ghost" size="sm" icon="eye" :tooltip="__('View deliverables')" />
                        </x-ui.data-table.action-cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </x-ui.data-table>
    @endif
</div>