<div class="handoff-sidebar__footer">
    <x-sidebar.theme-toggle />

    <x-sidebar.action icon="cog" :href="route('profile.edit')" :label="__('Settings')"
        :current="request()->routeIs('profile.edit', 'security.edit', 'passkeys.edit')" />

    <x-sidebar.logout />
</div>