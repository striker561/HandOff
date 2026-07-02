<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="handoff-canvas min-h-screen antialiased">
    <div class="flex min-h-svh items-center justify-center p-4 sm:p-8">
        <div class="handoff-clip-wrap w-full">
            <div class="handoff-clip-frame handoff-clip-frame--ticks">
                <div class="handoff-clip-shell">
                    <div class="handoff-clip-form">
                        <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3" wire:navigate>
                            <span
                                class="handoff-clip flex size-10 items-center justify-center bg-brand-700 text-white shadow-sm dark:bg-brand-600">
                                <x-app-logo-icon class="size-6 text-white" />
                            </span>
                            <flux:heading size="sm">{{ config('app.name') }}</flux:heading>
                        </a>

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @persist('toast')
    <flux:toast.group>
        <flux:toast />
    </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>