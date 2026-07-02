<flux:sidebar.header class="handoff-sidebar__header">
    <x-sidebar.logo />
    <flux:sidebar.collapse class="handoff-sidebar__collapse lg:hidden" />
</flux:sidebar.header>

<flux:sidebar.nav>
    <flux:sidebar.group :heading="__('Workspace')" class="grid">
        <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
            :accent="false" wire:navigate>
            {{ __('Dashboard') }}
        </flux:sidebar.item>
    </flux:sidebar.group>

    <flux:separator class="handoff-sidebar__separator" />

    <flux:sidebar.group :heading="__('Projects')" class="grid">
        <flux:sidebar.item icon="folder" :href="route('dashboard')" :current="false" :badge="__('Soon')" :accent="false"
            wire:navigate>
            {{ __('My projects') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>

<flux:spacer />

@include('layouts.app.sidebars._footer')