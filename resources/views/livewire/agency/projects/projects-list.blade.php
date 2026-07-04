<div>
    <x-ui.page-header :heading="__('Projects')" :subheading="__('Manage projects for your clients.')">
        <x-slot:actions>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search projects…') }}"
                icon="magnifying-glass" class="w-full sm:max-w-xs" />

            <flux:modal.trigger name="create-project" class="shrink-0">
                <x-ui.button icon="plus" class="!w-auto">
                    {{ __('Create') }}
                </x-ui.button>
            </flux:modal.trigger>
        </x-slot:actions>
    </x-ui.page-header>

    @if (count($this->projects) > 0)
        <div class="overflow-x-auto my-5">
            <flux:table :paginate="$this->projects">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sort === 'name'" :direction="$sort === 'name' ? $direction : null"
                        wire:click="sortBy('name')">
                        {{ __('Name') }}
                    </flux:table.column>
                    <flux:table.column class="hidden md:table-cell">{{ __('Client') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sort === 'budget'"
                        :direction="$sort === 'budget' ? $direction : null" wire:click="sortBy('budget')"
                        class="hidden md:table-cell">
                        {{ __('Budget') }}
                    </flux:table.column>
                    <flux:table.column sortable :sorted="$sort === 'due_date'"
                        :direction="$sort === 'due_date' ? $direction : null" wire:click="sortBy('due_date')"
                        class="hidden lg:table-cell">
                        {{ __('Due') }}
                    </flux:table.column>
                    <flux:table.column class="w-16">{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->projects as $project)
                        <flux:table.row :key="$project->unique_id">
                            <flux:table.cell variant="strong">
                                <div class="min-w-0">
                                    <div class="truncate">{{ $project->name }}</div>
                                    <div class="truncate text-sm text-zinc-500 md:hidden">{{ $project->list_summary }}</div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="hidden md:table-cell">
                                {{ $project->client_display_name }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$project->status->badgeColor()" size="sm">
                                    {{ $project->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="hidden md:table-cell">
                                @if ($project->formatted_budget)
                                    {{ $project->formatted_budget }}
                                @else
                                    <span class="text-zinc-300 dark:text-zinc-600">&mdash;</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="hidden lg:table-cell">
                                {{ $project->formatted_due_date ?? __('No date') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <x-ui.button icon="eye" wire:click="viewProject('{{ $project->unique_id }}')" class="px-3"
                                    aria-label="{{ __('View :name', ['name' => $project->name]) }}">
                                    <span class="sr-only">{{ __('View details') }}</span>
                                </x-ui.button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @else
        <x-ui.empty-state icon="folder" :heading="$search || $filterStatus ? __('No projects match your search') : __('No projects yet')" :text="$search || $filterStatus ? __('Try adjusting your search or filters.') : __('Create your first project to start collaborating with clients.')">
            <x-slot:actions>
                <flux:modal.trigger name="create-project">
                    <x-ui.button icon="plus">
                        {{ __('Create Project') }}
                    </x-ui.button>
                </flux:modal.trigger>
            </x-slot:actions>
        </x-ui.empty-state>
    @endif
</div>