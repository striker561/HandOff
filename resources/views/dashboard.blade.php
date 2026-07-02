@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $firstName = strtok($user->name, ' ');
@endphp

<x-layouts::app :title="__('Dashboard')">
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-8 py-2">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl">
                    {{ __('Hey :name', ['name' => $firstName]) }}
                </flux:heading>
                <flux:text class="text-brand-700/70 dark:text-brand-200/70">
                    @if ($isAdmin)
                        {{ __('Your agency workspace — clients, projects, and handoffs start here.') }}
                    @else
                        {{ __('Your client portal — deliverables and updates from your agency will show up here.') }}
                    @endif
                </flux:text>
            </div>

            <x-handoff-button :href="route('profile.edit')" variant="outline" icon="cog" class="!w-auto sm:shrink-0"
                wire:navigate>
                {{ __('Settings') }}
            </x-handoff-button>
        </div>

        <div class="handoff-clip handoff-card-clip p-10 text-center sm:p-12">
            <span
                class="mx-auto mb-6 flex size-14 items-center justify-center bg-brand-600 text-white handoff-clip dark:bg-brand-500">
                <x-app-logo-icon class="size-8 text-white" />
            </span>

            @if ($isAdmin)
                <flux:heading size="lg" class="mb-2">{{ __('Agency tools are on the way') }}</flux:heading>
                <flux:text class="mx-auto max-w-md text-brand-700/70 dark:text-brand-200/70">
                    {{ __('Client management, project boards, and credential vaults will land here. You’re building the foundation early.') }}
                </flux:text>
            @else
                <flux:heading size="lg" class="mb-2">{{ __('Projects are on the way') }}</flux:heading>
                <flux:text class="mx-auto max-w-md text-brand-700/70 dark:text-brand-200/70">
                    {{ __('Milestones, deliverables, and shared credentials from your agency will appear here.') }}
                </flux:text>
            @endif

            <div class="mx-auto mt-8 w-full max-w-xs">
                <x-handoff-button :href="route('profile.edit')" icon="user" wire:navigate>
                    {{ __('Set up your profile') }}
                </x-handoff-button>
            </div>
        </div>
    </div>
</x-layouts::app>