<x-layouts::app :title="__('Clients')">
    <div class="handoff-page handoff-page--wide">
        <livewire:agency.clients.clients-list />
    </div>

    <livewire:agency.clients.create-client />
    <livewire:agency.clients.view-client />
</x-layouts::app>