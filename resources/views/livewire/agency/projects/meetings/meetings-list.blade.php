<div>
    <x-agency.project-hub.section :heading="__('Meetings')">
        <x-slot:actions>
            <x-ui.button wire:click="openScheduleMeeting" icon="plus" class="!w-auto">
                {{ __('Schedule meeting') }}
            </x-ui.button>
        </x-slot:actions>

        @if ($this->meetings->isEmpty())
            <div class="project-overview__empty">
                <flux:text>{{ __('Schedule meetings for this project, optionally linked to a deliverable.') }}
                </flux:text>
            </div>
        @else
            <x-ui.data-table :paginate="$this->meetings" :panel="false">
                <flux:table.columns>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
                    <flux:table.column class="hidden md:table-cell">{{ __('Scheduled') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Duration') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Deliverable') }}</flux:table.column>
                    <flux:table.column class="handoff-data-table__action hidden w-16 sm:table-cell">
                        <span class="sr-only">{{ __('Actions') }}</span>
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->meetings as $meeting)
                        <flux:table.row :key="$meeting->unique_id">
                            <x-ui.data-table.primary-cell :title="$meeting->title" :meta="$meeting->scheduled_at?->format('M j, Y g:i A')">
                                <x-slot:mobile>
                                    <flux:badge :color="$meeting->status->badgeColor()" size="sm">
                                        {{ $meeting->status->label() }}
                                    </flux:badge>
                                </x-slot:mobile>
                            </x-ui.data-table.primary-cell>

                            <flux:table.cell class="hidden sm:table-cell">
                                <flux:badge :color="$meeting->status->badgeColor()" size="sm">
                                    {{ $meeting->status->label() }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="hidden md:table-cell">
                                {{ $meeting->scheduled_at?->format('M j, Y g:i A') ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell class="hidden lg:table-cell">
                                {{ $meeting->duration_minutes }} {{ __('min') }}
                            </flux:table.cell>

                            <flux:table.cell class="hidden lg:table-cell">
                                {{ $meeting->deliverable?->name ?? '—' }}
                            </flux:table.cell>

                            <x-ui.data-table.action-cell class="w-16">
                                @if ($meeting->status === \App\Enums\Meeting\MeetingStatus::SCHEDULED)
                                    <x-ui.button variant="outline" icon="pencil-square"
                                        wire:click="editMeeting('{{ $meeting->unique_id }}')"
                                        class="!w-auto px-3 py-2">
                                        <span class="sr-only">{{ __('Edit') }}</span>
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
