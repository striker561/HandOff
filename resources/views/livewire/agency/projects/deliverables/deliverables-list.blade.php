<div>
    <x-agency.project-hub.section :heading="__('Deliverables')" flush>
        <x-slot:description>
            {{ __('Files and assets tied to milestones — approve when ready for your client.') }}
        </x-slot:description>
        <x-slot:actions>
            @if ($this->hasMilestones)
                <x-ui.button wire:click="openSaveDeliverable" icon="plus" class="sm:!w-auto">
                    {{ __('Add deliverable') }}
                </x-ui.button>
            @endif
        </x-slot:actions>

        @if ($this->deliverables->isEmpty())
            @if (!$this->hasMilestones)
                <x-ui.empty-state compact icon="document-text" :heading="__('Add milestones first')" :text="__('Deliverables belong to a milestone. Set up your handoff phases before adding files and assets.')">
                    <x-slot:actions>
                        <x-ui.button :href="route('agency.projects.milestones', ['projectUniqueId' => $projectUniqueId])"
                            wire:navigate icon="flag" class="sm:!w-auto">
                            {{ __('Go to milestones') }}
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state compact icon="document-text" :heading="__('No deliverables yet')" :text="__('Upload files, links, or other assets and link them to a milestone. Approve each item when it is ready for your client to access.')">
                    <x-slot:actions>
                        <x-ui.button wire:click="openSaveDeliverable" icon="plus" class="sm:!w-auto">
                            {{ __('Add deliverable') }}
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.empty-state>
            @endif
        @else
            <x-ui.data-table :paginate="$this->deliverables" :panel="false" flush>
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column class="hidden md:table-cell">{{ __('Milestone') }}</flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">{{ __('Type') }}</flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Due date') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Version') }}</flux:table.column>
                    <flux:table.column class="handoff-data-table__action hidden sm:table-cell">
                        <span class="sr-only">{{ __('Actions') }}</span>
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->deliverables as $deliverable)
                        <flux:table.row :key="$deliverable->unique_id">
                            <x-ui.data-table.primary-cell :title="$deliverable->name"
                                :meta="collect([
                                    $deliverable->milestone?->name ?? __('No milestone'),
                                    $deliverable->due_date?->format('M j, Y') ?? __('No due date'),
                                    'v' . $deliverable->version,
                                ])->join(' · ')">
                                <x-slot:mobile>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-md bg-brand-100/80 px-2 py-1 text-xs font-medium text-brand-800 dark:bg-brand-900/60 dark:text-brand-100">
                                            <x-dynamic-component :component="'flux::icon.' . $deliverable->type->icon()" variant="mini"
                                                class="size-3.5 shrink-0" />
                                            {{ $deliverable->type->label() }}
                                        </span>
                                        <flux:badge :color="$deliverable->status->badgeColor()" size="sm">
                                            {{ $deliverable->status->label() }}
                                        </flux:badge>
                                    </div>
                                </x-slot:mobile>
                                <x-slot:action>
                                    @include('livewire.agency.projects.deliverables._deliverable-row-actions', [
                                        'deliverable' => $deliverable,
                                        'mobile' => true,
                                    ])
                                </x-slot:action>
                            </x-ui.data-table.primary-cell>

                            <flux:table.cell class="hidden md:table-cell">
                                {{ $deliverable->milestone?->name ?? __('No milestone') }}
                            </flux:table.cell>

                            <flux:table.cell class="hidden sm:table-cell">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-dynamic-component :component="'flux::icon.' . $deliverable->type->icon()" variant="mini"
                                        class="size-4 shrink-0 text-zinc-500" />
                                    {{ $deliverable->type->label() }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell class="hidden sm:table-cell">
                                <flux:badge :color="$deliverable->status->badgeColor()" size="sm">
                                    {{ $deliverable->status->label() }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="hidden lg:table-cell">
                                {{ $deliverable->due_date?->format('M j, Y') ?? __('No due date') }}
                            </flux:table.cell>

                            <flux:table.cell class="hidden lg:table-cell">
                                v{{ $deliverable->version }}
                            </flux:table.cell>

                            <x-ui.data-table.action-cell>
                                @include('livewire.agency.projects.deliverables._deliverable-row-actions', [
                                    'deliverable' => $deliverable,
                                ])
                            </x-ui.data-table.action-cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </x-ui.data-table>
        @endif
    </x-agency.project-hub.section>
</div>
