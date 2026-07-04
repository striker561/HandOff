<x-layouts::app :title="__('Projects')">
    <div class="handoff-page handoff-page--wide">
        <livewire:agency.projects.projects-list />
    </div>

    <livewire:agency.projects.create-project />
    <livewire:agency.projects.view-project />
</x-layouts::app>