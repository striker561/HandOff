<x-agency.project-hub.shell :project="$project" :section="$section">
    <livewire:agency.projects.meetings.meetings-list :project-unique-id="$project->unique_id" />

    <x-slot:modals>
        <livewire:agency.projects.meetings.schedule-meeting />
    </x-slot:modals>
</x-agency.project-hub.shell>