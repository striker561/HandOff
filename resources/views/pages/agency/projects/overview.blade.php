<x-agency.project-hub.shell :project="$project" :section="$section">
    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <flux:text class="text-sm font-medium">{{ __('Client') }}</flux:text>
            <flux:text class="mt-1">{{ $project->client_display_name }}</flux:text>
        </div>
        <div>
            <flux:text class="text-sm font-medium">{{ __('Budget') }}</flux:text>
            <flux:text class="mt-1">
                @if ($project->formatted_budget)
                    {{ $project->formatted_budget }}
                @else
                    <x-ui.data-table.empty />
                @endif
            </flux:text>
        </div>
        <div>
            <flux:text class="text-sm font-medium">{{ __('Start date') }}</flux:text>
            <flux:text class="mt-1">{{ $project->start_date?->format('M j, Y') ?? __('No date') }}</flux:text>
        </div>
        <div>
            <flux:text class="text-sm font-medium">{{ __('Due date') }}</flux:text>
            <flux:text class="mt-1">{{ $project->formatted_due_date ?? __('No date') }}</flux:text>
        </div>
        <div class="sm:col-span-2">
            <flux:text class="text-sm font-medium">{{ __('Progress') }}</flux:text>
            <flux:text class="mt-1">{{ $project->progress_percentage }}%</flux:text>
        </div>
        @if ($project->description)
            <div class="sm:col-span-2">
                <flux:text class="text-sm font-medium">{{ __('Description') }}</flux:text>
                <flux:text class="mt-1">{{ $project->description }}</flux:text>
            </div>
        @endif
    </dl>
</x-agency.project-hub.shell>