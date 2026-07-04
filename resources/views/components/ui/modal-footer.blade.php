@props([
    'align' => 'end',
])
@php
    $alignClass = match ($align) {
        'start' => 'justify-start',
        'center' => 'justify-center',
        'end' => 'justify-end',
        default => 'justify-end',
    };
@endphp
<div
    {{ $attributes->class([
        'flex items-center gap-2 border-t border-brand-200/60 pt-6 dark:border-brand-800/50',
        $alignClass,
    ]) }}>
    {{ $slot }}
</div>
