<div>
    @if ($this->meetings->isEmpty())
        <x-ui.empty-state icon="calendar-days" :heading="__('No meetings scheduled')" :text="__('Schedule meetings for this project, optionally linked to a deliverable.')">
            <x-slot:actions>
                <flux:button wire:click="openScheduleMeeting" variant="primary" icon="plus">
                    {{ __('Schedule meeting') }}
                </flux:button>
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <x-ui.page-header :heading="__('Meetings')" :subheading="__('Scheduled meetings for this project.')">
            <x-slot:actions>
                <flux:button wire:click="openScheduleMeeting" variant="primary" icon="plus">
                    {{ __('Schedule meeting') }}
                </flux:button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.data-table :paginate="$this->meetings" class="mt-6">
            <flux:table.columns>
                <flux:table.column>{{ __('Title') }}</flux:table.column>
                <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
                <flux:table.column class="hidden md:table-cell">{{ __('Scheduled') }}</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">{{ __('Duration') }}</flux:table.column>
                <flux:table.column class="hidden lg:table-cell">{{ __('Deliverable') }}</flux:table.column>
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
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </x-ui.data-table>
    @endif
</div>