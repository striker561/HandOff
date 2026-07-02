@props([
    'size' => 'md',
    'variant' => 'default',
])

@php
    $boxSizes = [
        'sm' => 'size-8',
        'md' => 'size-10',
        'lg' => 'size-11',
        'xl' => 'size-12',
        'hero' => 'size-24',
    ];

    $iconSizes = [
        'sm' => 'size-5',
        'md' => 'size-6',
        'lg' => 'size-6',
        'xl' => 'size-6',
        'hero' => 'size-14',
    ];
@endphp

<span {{ $attributes->class([
    'handoff-logo-mark',
    'handoff-logo-mark--sidebar' => $variant === 'sidebar',
    $boxSizes[$size] ?? $boxSizes['md'],
]) }}>
    <x-app-logo-icon :class="($iconSizes[$size] ?? $iconSizes['md']) . ' text-white'" />
</span>
