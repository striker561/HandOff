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

            <x-ui.confirm-modal
                name="confirm-delete-milestone"
                :heading="__('Delete milestone?')"
                :description="__('This cannot be undone. Delete any deliverables linked to this milestone first.')"
                :confirm-label="__('Delete')"
                confirm-action="deleteMilestone"
                cancel-action="cancelDeleteMilestone"
            />

            <div class="px-4 pb-4 sm:px-5">
                <x-ui.hint-disclosure :heading="__('How milestones work')">
                    <ul class="handoff-hint-disclosure__list">
                        <li>{{ __('Set each phase to Pending or In progress. Completed is automatic.') }}</li>
                        <li>{{ __('When every deliverable in a milestone is client-approved, the milestone completes.') }}</li>
                        <li>{{ __('Adding a deliverable to a completed milestone moves it back to In progress.') }}</li>
                        <li>{{ __('Due date can be changed until deliverables are linked to the milestone.') }}</li>
                        <li>{{ __('Delete empty milestones from the row actions. Remove linked deliverables first.') }}</li>
                    </ul>
                </x-ui.hint-disclosure>
            </div>
        @endif
    </x-agency.project-hub.section>
</div>
