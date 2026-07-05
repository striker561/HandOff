<div>
    <x-agency.project-hub.section :heading="__('Milestones')" flush>
        <x-slot:description>
            {{ __('Phases of the handoff — clients see progress as each milestone completes.') }}
        </x-slot:description>
        <x-slot:actions>
            <x-ui.button wire:click="openSaveMilestone" icon="plus" class="sm:!w-auto">
                {{ __('Add milestone') }}
            </x-ui.button>
        </x-slot:actions>

        @if ($this->milestones->isEmpty())
            <x-ui.empty-state compact icon="flag" :heading="__('No milestones yet')" :text="__(
                'Break the project into ordered phases. Each milestone can hold deliverables your client reviews and approves.',
            )">
                <x-slot:actions>
                    <x-ui.button wire:click="openSaveMilestone" icon="plus" class="sm:!w-auto">
                        {{ __('Add milestone') }}
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        @else
            <x-ui.data-table :paginate="$this->milestones" :panel="false" flush>
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
                    <flux:table.column class="hidden md:table-cell">{{ __('Due date') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Deliverables') }}</flux:table.column>
                    <flux:table.column class="handoff-data-table__action hidden sm:table-cell">
                        <span class="sr-only">{{ __('Actions') }}</span>
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->milestones as $milestone)
                        <flux:table.row :key="$milestone->unique_id">
                            <x-ui.data-table.primary-cell :title="$milestone->name" :mobile-title="$milestone->name .
                                ' · ' .
                                trans_choice(':count deliverable|:count deliverables', $milestone->deliverables_count, [
                                    'count' => $milestone->deliverables_count,
                                ])" :meta="$milestone->due_date?->format('M j, Y') ?? __('No due date')"
                                :href="route('agency.projects.deliverables', [
                                    'projectUniqueId' => $projectUniqueId,
                                    'milestone' => $milestone->unique_id,
                                ])">
                                <x-slot:mobile>
                                    <flux:badge :color="$milestone->status->badgeColor()" size="sm">
                                        {{ $milestone->status->label() }}
                                    </flux:badge>
                                </x-slot:mobile>
                                <x-slot:action>
                                    @include('livewire.agency.projects.milestones._milestone-row-actions', [
                                        'milestone' => $milestone,
                                        'mobile' => true,
                                    ])
                                </x-slot:action>
                            </x-ui.data-table.primary-cell>

                            <flux:table.cell class="hidden sm:table-cell">
                                <flux:badge :color="$milestone->status->badgeColor()" size="sm">
                                    {{ $milestone->status->label() }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="hidden md:table-cell">
                                {{ $milestone->due_date?->format('M j, Y') ?? __('No due date') }}
                            </flux:table.cell>

                            <flux:table.cell class="hidden lg:table-cell">
                                {{ $milestone->deliverables_count }}
                            </flux:table.cell>

                            <x-ui.data-table.action-cell>
                                @include('livewire.agency.projects.milestones._milestone-row-actions', [
                                    'milestone' => $milestone,
                                ])
                            </x-ui.data-table.action-cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </x-ui.data-table>


            @php
                $statusHelpLine = __('Agency sets milestones to :pending or :in_progress.', [
                    'pending' => __('Pending'),
                    'in_progress' => __('In progress'),
                ]);
            @endphp
            <div x-data="{ open: false }" class="mb-4">
                <button @click="open = !open" t y pe="button"
                    class="text-brand-700/70 dark:text-brand-200/70 hover:text-brand-900 dark:hover:text-brand-50 flex w-full items-center gap-2 rounded-lg border px-3 py-2 text-sm transition">
                    <flux:icon.information-circle variant="mini" class="size-4 shrink-0" />
                    <span>{{ __('How milestone status works') }}</span>

                    <flux:icon.chevron-down variant="mini" class="ml-auto size-4 transition"
                        x-bind:class="open && 'rotate-180'" />
                </button>
                <div x-show="open" x-transition
                    class="text-brand-700/70 dark:text-brand-200/70 mt-2 space-y-1.5 rounded-lg border border-t-0 px-3 py-3 text-sm">
                    <p>{{ $statusHelpLine }}</p>
                    <p>{{ __('When all deliverables in a milestone are approved by the client, the milestone auto-completes.') }}
                    </p>
                    <p>{{ __('Adding a new deliverable to a completed milestone moves it back to in progress.') }}</p>
                </div>
            </div>
        @endif
    </x-agency.project-hub.section>
</div>
