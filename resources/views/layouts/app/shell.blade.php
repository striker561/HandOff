@props([
    'title' => null,
    'variant' => 'portal',
    'content' => '',
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
            'handoff-sidebar--' . $variant,
        ])>
        @include('layouts.app.sidebars.sidebar', ['variant' => $variant])
    </flux:sidebar>

    @include('layouts.app.headers.' . $variant)

    <flux:main class="handoff-main">
        {!! $content !!}
    </flux:main>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
