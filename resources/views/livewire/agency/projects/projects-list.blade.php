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
                    <flux:table.column>{{ __('Client') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sort === 'status'"
                        :direction="$sort === 'status' ? $direction : null" wire:click="sortBy('status')">
                        {{ __('Status') }}
                    </flux:table.column>
                    <flux:table.column sortable :sorted="$sort === 'budget'"
                        :direction="$sort === 'budget' ? $direction : null" wire:click="sortBy('budget')">
                        {{ __('Budget') }}
                    </flux:table.column>
                    <flux:table.column sortable :sorted="$sort === 'due_date'"
                        :direction="$sort === 'due_date' ? $direction : null" wire:click="sortBy('due_date')">
                        {{ __('Due') }}
                    </flux:table.column>
                    <flux:table.column class="w-14">{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->projects as $project)
                        <flux:table.row :key="$project->unique_id">
                            <flux:table.cell variant="strong">{{ $project->name }}</flux:table.cell>
                            <flux:table.cell>{{ $project->client?->name ?? __('Unknown') }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $statusColors = [
                                        'planning' => 'gray',
                                        'active' => 'blue',
                                        'on_hold' => 'amber',
                                        'completed' => 'lime',
                                        'cancelled' => 'red',
                                    ];
                                    $color = $statusColors[$project->status->value] ?? 'gray';
                                @endphp
                                <flux:badge :color="$color" size="sm">
                                    {{ __(ucfirst($project->status->value)) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($project->budget)
                                    @php
                                        $symbols = ['usd' => '$', 'ngn' => '₦', 'eur' => '€'];
                                        $symbol = $symbols[$project->currency->value] ?? '$';
                                    @endphp
                                    {{ $symbol }}{{ number_format($project->budget, 2) }}
                                @else
                                    <span class="text-brand-400 dark:text-brand-600">&mdash;</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $project->due_date?->format('M j, Y') ?? __('No date') }}
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