<x-agency.project-hub.shell :project="$project" :section="$section">
    @if ($milestoneUniqueId)
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <flux:badge color="zinc" size="sm">{{ __('Filtered by milestone') }}</flux:badge>
            <x-ui.button :href="route('agency.projects.deliverables', ['projectUniqueId' => $project->unique_id])" wire:navigate variant="outline" class="!w-auto px-3 py-1.5 text-xs sm:text-sm">
                {{ __('Clear filter') }}
            </x-ui.button>
        </div>
    @endif

    <livewire:agency.projects.deliverables.deliverables-list :project-unique-id="$project->unique_id" :milestone-unique-id="$milestoneUniqueId" />

    <x-slot:modals>
        <livewire:agency.projects.deliverables.save-deliverable />
    </x-slot:modals>
</x-agency.project-hub.shell>
