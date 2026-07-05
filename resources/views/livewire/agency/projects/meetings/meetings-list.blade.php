<div>
    <x-agency.project-hub.section :heading="__('Meetings')" flush>
        <x-slot:description>
            {{ __('Check-ins linked to this project or a specific deliverable.') }}
        </x-slot:description>
        <x-slot:actions>
            <x-ui.button wire:click="openSaveMeeting" icon="plus" class="sm:!w-auto">
                {{ __('Schedule meeting') }}
            </x-ui.button>
        </x-slot:actions>

        @if ($this->meetings->isEmpty())
            <x-ui.empty-state compact icon="calendar-days" :heading="__('No meetings scheduled')" :text="__('Plan kickoffs, reviews, and handoff calls. Optionally link a meeting to a deliverable so context stays clear.')">
                <x-slot:actions>
                    <x-ui.button wire:click="openSaveMeeting" icon="plus" class="sm:!w-auto">
                        {{ __('Schedule meeting') }}
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        @else
            <x-ui.data-table :paginate="$this->meetings" :panel="false" flush>
                <flux:table.columns>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
                    <flux:table.column class="hidden md:table-cell">{{ __('Scheduled') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Duration') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Deliverable') }}</flux:table.column>
                    <flux:table.column class="handoff-data-table__action hidden sm:table-cell">
                        <span class="sr-only">{{ __('Actions') }}</span>
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->meetings as $meeting)
                            <flux:table.row :key="$meeting->unique_id">
                                <x-ui.data-table.primary-cell :title="$meeting->title" :meta="collect([
                            $meeting->scheduled_at?->format('M j, Y g:i A') ?? __('Not scheduled'),
                            $meeting->duration_minutes ? $meeting->duration_minutes . ' ' . __('min') : null,
                            $meeting->deliverable?->name ?? __('No deliverable'),
                        ])->filter()->join(' · ')">
                                    <x-slot:mobile>
                                        <flux:badge :color="$meeting->status->badgeColor()" size="sm">
                                            {{ $meeting->status->label() }}
                                        </flux:badge>
                                    </x-slot:mobile>
                                    @if ($meeting->status === \App\Enums\Meeting\MeetingStatus::SCHEDULED)
                                        <x-slot:action>
                                            <x-ui.button icon="pencil-square" wire:click="editMeeting('{{ $meeting->unique_id }}')">
                                                {{ __('Edit meeting') }}
                                            </x-ui.button>
                                        </x-slot:action>
                                    @endif
                                </x-ui.data-table.primary-cell>

                                <flux:table.cell class="hidden sm:table-cell">
                                    <flux:badge :color="$meeting->status->badgeColor()" size="sm">
                                        {{ $meeting->status->label() }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell class="hidden md:table-cell">
                                    {{ $meeting->scheduled_at?->format('M j, Y g:i A') ?? __('Not scheduled') }}
                                </flux:table.cell>

                                <flux:table.cell class="hidden lg:table-cell">
                                    {{ $meeting->duration_minutes }} {{ __('min') }}
                                </flux:table.cell>

                                <flux:table.cell class="hidden lg:table-cell">
                                    {{ $meeting->deliverable?->name ?? __('No deliverable') }}
                                </flux:table.cell>

                                <x-ui.data-table.action-cell>
                                    @if ($meeting->status === \App\Enums\Meeting\MeetingStatus::SCHEDULED)
                                        <x-ui.button icon="pencil-square" icon-only
                                            wire:click="editMeeting('{{ $meeting->unique_id }}')">
                                            {{ __('Edit') }}
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