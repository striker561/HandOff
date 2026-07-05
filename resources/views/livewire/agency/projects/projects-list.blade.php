<div>
    <x-ui.page-header :heading="__('Projects')" :subheading="__('Manage projects for your clients.')">
        <x-slot:actions>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search projects…') }}"
                icon="magnifying-glass" class="w-full sm:max-w-xs" />

            <x-ui.button wire:click="openSaveProject" icon="plus" class="shrink-0 sm:!w-auto">
                {{ __('Create') }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if (count($this->projects) > 0)
        <x-ui.data-table :paginate="$this->projects">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sort === 'name'" :direction="$sort === 'name' ? $direction : null"
                    wire:click="sortBy('name')">
                    {{ __('Name') }}
                </flux:table.column>
                <flux:table.column class="hidden md:table-cell">{{ __('Client') }}</flux:table.column>
                <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'budget'" :direction="$sort === 'budget' ? $direction : null"
                    wire:click="sortBy('budget')" class="hidden md:table-cell">
                    {{ __('Budget') }}
                </flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'due_date'"
                    :direction="$sort === 'due_date' ? $direction : null" wire:click="sortBy('due_date')"
                    class="hidden lg:table-cell">
                    {{ __('Due') }}
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->projects as $project)
                    <flux:table.row :key="$project->unique_id">
                        <x-ui.data-table.primary-cell :title="$project->name"
                            wire-click="viewProject('{{ $project->unique_id }}')">
                            <x-slot:mobile>
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span class="text-sm font-normal text-zinc-500">{{ $project->client_display_name }}</span>
                                    <flux:badge :color="$project->status->badgeColor()" size="sm">
                                        {{ $project->status->label() }}
                                    </flux:badge>
                                </div>
                            </x-slot:mobile>
                        </x-ui.data-table.primary-cell>

                        <flux:table.cell class="hidden md:table-cell">
                            {{ $project->client_display_name }}
                        </flux:table.cell>

                        <flux:table.cell class="hidden sm:table-cell">
                            <flux:badge :color="$project->status->badgeColor()" size="sm">
                                {{ $project->status->label() }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="hidden md:table-cell">
                            @if ($project->formatted_budget)
                                {{ $project->formatted_budget }}
                            @else
                                <x-ui.data-table.empty />
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="hidden lg:table-cell">
                            {{ $project->formatted_due_date ?? __('No due date') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </x-ui.data-table>
    @else
        <x-ui.empty-state icon="folder" :heading="$search || $filterStatus ? __('No projects match your search') : __('No projects yet')" :text="$search || $filterStatus ? __('Try adjusting your search or filters.') : __('Create your first project to start collaborating with clients.')">
            <x-slot:actions>
                <x-ui.button wire:click="openSaveProject" icon="plus">
                    {{ __('Create Project') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.empty-state>
    @endif
</div>