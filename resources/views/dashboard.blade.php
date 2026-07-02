@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $firstName = strtok($user->name, ' ');
@endphp

<x-layouts.workspace :title="__('Dashboard')">
    <div @class([
        'handoff-page',
        'handoff-page--wide' => $isAdmin,
    ])>
        <header class="handoff-page__header">
            <div class="space-y-1">
                <flux:heading size="xl">
                    {{ __('Hey :name', ['name' => $firstName]) }}
                </flux:heading>
                <flux:text class="handoff-page__lede">
                    @if ($isAdmin)
                        {{ __('Your agency workspace. Clients, projects, and handoffs start here.') }}
                    @else
                        {{ __('Your client portal. Deliverables and updates from your agency will show up here.') }}
                    @endif
                </flux:text>
            </div>

            <x-ui.button :href="route('profile.edit')" variant="outline" icon="cog" class="!w-auto sm:shrink-0"
                wire:navigate>
                {{ __('Settings') }}
            </x-ui.button>
        </header>

        @if ($isAdmin)
            <div class="handoff-stat-grid">
                <x-dashboard.stat-card :label="__('Clients')" icon="users" value="0" />
                <x-dashboard.stat-card :label="__('Projects')" icon="folder" value="0" />
                <x-dashboard.stat-card :label="__('Deliverables')" icon="document-text" value="0" />
                <x-dashboard.stat-card :label="__('Updates')" icon="chat-bubble-left-right" value="0" />
            </div>
        @endif

        <flux:callout icon="sparkles" color="indigo" class="handoff-dashboard-callout">
            <x-slot name="heading">
                @if ($isAdmin)
                    {{ __('Agency tools are on the way') }}
                @else
                    {{ __('Projects are on the way') }}
                @endif
            </x-slot>

            <x-slot name="text">
                @if ($isAdmin)
                    {{ __('Client management, project boards, and credential vaults will land here. You are building the foundation early.') }}
                @else
                    {{ __('Milestones, deliverables, and shared credentials from your agency will appear here.') }}
                @endif
            </x-slot>

            <x-slot name="actions">
                <x-ui.button :href="route('profile.edit')" icon="user" class="!w-auto" wire:navigate>
                    {{ __('Set up your profile') }}
                </x-ui.button>
            </x-slot>
        </flux:callout>
    </div>
</x-layouts.workspace>