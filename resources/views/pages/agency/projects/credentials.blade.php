<x-agency.project-hub.shell :project="$project" :section="$section">
    <livewire:agency.projects.credentials.credentials-list :project-unique-id="$project->unique_id" />

    <x-slot:modals>
        <livewire:agency.projects.credentials.save-credential />
        <livewire:agency.projects.credentials.view-credential />
    </x-slot:modals>
</x-agency.project-hub.shell>
