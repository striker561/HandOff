@props([
    'title' => null,
    'workspace' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="handoff-app min-h-screen">
    <flux:sidebar sticky collapsible="mobile"
        @class([
            'handoff-sidebar',
            'handoff-sidebar--' . $workspace->value,
        ])>
        @include('layouts.app.sidebars.' . $workspace->value)
    </flux:sidebar>

    @include('layouts.app.headers.' . $workspace->value)

    <flux:main class="handoff-main">
        {{ $slot }}
    </flux:main>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
