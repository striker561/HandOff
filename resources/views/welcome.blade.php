<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'HandOff') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="handoff-canvas min-h-screen text-brand-950 dark:text-brand-50">
    <div class="relative flex min-h-screen flex-col">
        <header class="relative z-10 flex items-center justify-between px-6 py-6 lg:px-12">
            <x-app-logo href="{{ route('home') }}" />

            @if (Route::has('login'))
                <nav class="flex items-center gap-3">
                    @auth
                        <x-ui.button :href="route('dashboard')" variant="outline" icon="home" class="!w-auto">
                            {{ __('Dashboard') }}
                        </x-ui.button>
                    @else
                        <x-ui.button :href="route('login')" icon="arrow-right-start-on-rectangle" class="!w-auto">
                            {{ __('Log in') }}
                        </x-ui.button>
                    @endauth
                </nav>
            @endif
        </header>

        <main class="relative z-10 flex flex-1 flex-col items-center justify-center px-6 pb-20 pt-4 lg:px-12">
            <div class="mx-auto grid w-full max-w-6xl items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 flex flex-col gap-8 lg:order-1">
                    <div class="space-y-4">
                        <flux:heading size="xl" class="text-balance text-4xl leading-tight sm:text-5xl">
                            {{ __('Pass projects to clients without the chaos') }}
                        </flux:heading>
                        <flux:text class="max-w-lg text-lg text-brand-800/70 dark:text-brand-200/70">
                            {{ __('HandOff is where deliverables, credentials, and updates land, so your clients always know what’s next.') }}
                        </flux:text>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @auth
                            <x-ui.button :href="route('dashboard')" icon="arrow-right" class="!w-auto">
                                {{ __('Go to dashboard') }}
                            </x-ui.button>
                        @else
                            <x-ui.button :href="route('login')" icon="arrow-right-start-on-rectangle" class="!w-auto">
                                {{ __('Sign in') }}
                            </x-ui.button>
                            <x-ui.button :href="route('login')" variant="outline" icon="archive-box" class="!w-auto">
                                {{ __('See how it works') }}
                            </x-ui.button>
                        @endauth
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2 sm:gap-5">
                        <x-marketing.feature-card :label="__('Deliverables')" icon="archive-box" />
                        <x-marketing.feature-card :label="__('Credentials')" icon="key" />
                        <x-marketing.feature-card :label="__('Meetings')" icon="calendar-days" />
                        <x-marketing.feature-card :label="__('Updates')" icon="chat-bubble-left-right" />
                    </div>
                </div>

                <div class="order-1 flex justify-center lg:order-2">
                    <div class="handoff-clip-wrap w-full max-w-xs sm:max-w-sm">
                        <div class="handoff-clip-frame handoff-clip-frame--ticks">
                            <div class="handoff-clip-shell">
                                <div class="p-10 text-center">
                                    <span
                                        class="handoff-clip mx-auto flex size-24 items-center justify-center bg-brand-700 text-white shadow-sm dark:bg-brand-600">
                                        <x-app-logo-icon class="size-14 text-white" />
                                    </span>
                                    <flux:text
                                        class="mt-6 text-sm font-medium text-brand-700/80 dark:text-brand-300/80">
                                        {{ __('Ready when you are.') }}
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="relative z-10 px-6 py-6 text-center lg:px-12">
            <flux:text class="text-sm text-brand-700/50 dark:text-brand-300/50">
                {{ __('Built for agencies who care about the handoff.') }}
            </flux:text>
        </footer>
    </div>

    @fluxScripts
</body>

</html>