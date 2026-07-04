<x-agency.project-hub.shell :project="$project" :section="$section">
    <livewire:agency.projects.milestones.milestones-list :project-unique-id="$project->unique_id" />

    <x-slot:modals>
        <livewire:agency.projects.milestones.create-milestone />
    </x-slot:modals>
</x-agency.project-hub.shell>