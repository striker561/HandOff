<x-agency.project-hub.shell :project="$project" :section="$section">
    @if ($milestoneUniqueId)
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <flux:badge color="zinc" size="sm">{{ __('Filtered by milestone') }}</flux:badge>
            <flux:button href="{{ route('agency.projects.deliverables', ['projectUniqueId' => $project->unique_id]) }}"
                wire:navigate variant="ghost" size="sm">
                {{ __('Clear filter') }}
            </flux:button>
        </div>
    @endif

    <livewire:agency.projects.deliverables.deliverables-list :project-unique-id="$project->unique_id"
        :milestone-unique-id="$milestoneUniqueId" />

    <x-slot:modals>
        <livewire:agency.projects.deliverables.create-deliverable />
    </x-slot:modals>
</x-agency.project-hub.shell>