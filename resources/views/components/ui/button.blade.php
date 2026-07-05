@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'icon' => null,
    'iconOnly' => false,
])
@php
    $isIconOnly = filter_var($iconOnly, FILTER_VALIDATE_BOOLEAN);

    $effectiveVariant = $isIconOnly ? 'primary' : $variant;

    $variantClass = match ($effectiveVariant) {
        'secondary' => 'handoff-btn-secondary',
        'outline' => 'handoff-btn-outline',
        default => 'handoff-btn-primary',
    };

    $classes = $attributes->class([
        'handoff-btn',
        $variantClass,
        'handoff-btn-action' => $effectiveVariant === 'primary',
        '!w-auto px-3 py-2' => $isIconOnly,
    ]);
@endphp
@if ($href)
        <a href="{{ $href }}" {{ $classes }}>
        @if ($icon)
            <x-dynamic-component :component="'flux::icon.' . $icon" variant="mini" class="size-4 shrink-0" />
        @endif
        <span @class(['sr-only' => $isIconOnly])>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $classes }}>
        @if ($icon)
            <x-dynamic-component :component="'flux::icon.' . $icon" variant="mini" class="size-4 shrink-0" />
        @endif
        <span @class(['sr-only' => $isIconOnly])>{{ $slot }}</span>
    </button>
@endif
