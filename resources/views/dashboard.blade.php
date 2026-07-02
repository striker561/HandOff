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

        <div class="handoff-panel">
            <div class="handoff-panel__body handoff-panel__body--centered">
                <span class="handoff-panel__mark">
                    <x-app-logo-icon class="size-8 text-white" />
                </span>

                @if ($isAdmin)
                    <flux:heading size="lg" class="mb-2">{{ __('Agency tools are on the way') }}</flux:heading>
                    <flux:text class="handoff-panel__copy">
                        {{ __('Client management, project boards, and credential vaults will land here. You are building the foundation early.') }}
                    </flux:text>
                @else
                    <flux:heading size="lg" class="mb-2">{{ __('Projects are on the way') }}</flux:heading>
                    <flux:text class="handoff-panel__copy">
                        {{ __('Milestones, deliverables, and shared credentials from your agency will appear here.') }}
                    </flux:text>
                @endif

                <div class="mx-auto mt-8 w-full max-w-xs">
                    <x-ui.button :href="route('profile.edit')" icon="user" wire:navigate>
                        {{ __('Set up your profile') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.workspace>