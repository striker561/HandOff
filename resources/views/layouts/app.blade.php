@php
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $layoutPath = $isAdmin ? 'layouts::app.admin.sidebar' : 'layouts::app.user.sidebar';
@endphp

<x-dynamic-component :component="$layoutPath" :title="$title ?? null">
    {{ $slot }}
</x-dynamic-component>