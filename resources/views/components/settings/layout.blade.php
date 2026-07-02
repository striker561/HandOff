@props([
    'heading' => null,
    'subheading' => null,
])

@php
    use Laravel\Fortify\Features;
@endphp

<div class="settings-layout">
    <header class="settings-layout__header">
        <div class="space-y-1">
            <flux:heading size="xl">{{ __('Settings') }}</flux:heading>
            <flux:text class="settings-layout__lede">
                {{ __('Manage your profile and account settings') }}
            </flux:text>
        </div>
    </header>

    <nav class="settings-layout__tabs" aria-label="{{ __('Settings sections') }}">
        <a href="{{ route('profile.edit') }}" wire:navigate
            @class([
                'settings-layout__tab',
                'settings-layout__tab--current' => request()->routeIs('profile.edit'),
            ])
            @if (request()->routeIs('profile.edit')) aria-current="page" @endif>
            {{ __('Profile') }}
        </a>
        <a href="{{ route('security.edit') }}" wire:navigate
            @class([
                'settings-layout__tab',
                'settings-layout__tab--current' => request()->routeIs('security.edit'),
            ])
            @if (request()->routeIs('security.edit')) aria-current="page" @endif>
            {{ __('Security') }}
        </a>
        @if (Features::canManagePasskeys())
            <a href="{{ route('passkeys.edit') }}" wire:navigate
                @class([
                    'settings-layout__tab',
                    'settings-layout__tab--current' => request()->routeIs('passkeys.edit'),
                ])
                @if (request()->routeIs('passkeys.edit')) aria-current="page" @endif>
                {{ __('Passkeys') }}
            </a>
        @endif
    </nav>

    <div class="settings-layout__card handoff-clip-wrap w-full max-w-none">
        <div class="handoff-clip-frame handoff-clip-frame--ticks">
            <div class="handoff-clip-shell">
                <section class="settings-layout__panel handoff-clip-form" aria-labelledby="settings-section-heading">
                    @if ($heading)
                        <header class="settings-layout__section-header" id="settings-section-heading">
                            <flux:heading size="lg">{{ $heading }}</flux:heading>
                            @if ($subheading)
                                <flux:text class="settings-layout__lede">{{ $subheading }}</flux:text>
                            @endif
                        </header>
                    @endif

                    <div class="settings-layout__content">
                        {{ $slot }}
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
