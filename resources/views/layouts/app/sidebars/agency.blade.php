<flux:sidebar.header class="handoff-sidebar__header">
    <x-sidebar.logo />
    <flux:sidebar.collapse class="handoff-sidebar__collapse lg:hidden" />
</flux:sidebar.header>

<nav class="handoff-sidebar-menu" aria-label="{{ __('Navigation') }}">
    <div class="handoff-sidebar-menu__group">
        <x-sidebar.nav-item icon="home" :href="route('dashboard')" :label="__('Dashboard')"
            :current="request()->routeIs('dashboard')" />

        <x-sidebar.nav-item icon="users" :href="route('dashboard')" :label="__('Clients')" :current="false"
            :badge="__('Soon')" />

        <x-sidebar.nav-item icon="folder" :href="route('dashboard')" :label="__('Projects')" :current="false"
            :badge="__('Soon')" />
    </div>
</nav>

<flux:spacer />

<div class="handoff-sidebar__footer">
    <x-sidebar.theme-toggle />

    <x-sidebar.action icon="cog" :href="route('profile.edit')" :label="__('Settings')"
        :current="request()->routeIs('profile.edit', 'security.edit', 'passkeys.edit')" />

    <x-sidebar.logout />
</div>