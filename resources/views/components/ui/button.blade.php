@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'icon' => null,
])
@php
    $variantClass = match ($variant) {
        'secondary' => 'handoff-btn-secondary',
        'outline' => 'handoff-btn-outline',
        default => 'handoff-btn-primary',
    };

    $classes = $attributes->class([
        'handoff-btn',
        $variantClass,
        'handoff-btn-action' => $variant === 'primary',
    ]);
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $classes }}>
            @if ($icon)
                <x-dynamic-component :component="'flux::icon.' . $icon" variant="mini" class="size-4 shrink-0" />
            @endif
    <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $classes }}>
            @if ($icon)
                <x-dynamic-component :component="'flux::icon.' . $icon" variant="mini" class="size-4 shrink-0" />
            @endif
            <span>{{ $slot }}</span>
        </button>
@endif
