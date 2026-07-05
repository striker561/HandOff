@props([
    'icon' => null,
    'heading' => null,
    'text' => null,
    'compact' => false,
])

<div @class([
    'flex flex-col items-center justify-center text-center',
    'py-24' => ! $compact,
    'py-8 sm:py-10' => $compact,
])>
    @if ($icon)
        <div @class([
            'flex items-center justify-center rounded-2xl bg-brand-100 dark:bg-brand-900',
            'mb-6 size-16' => ! $compact,
            'mb-4 size-12' => $compact,
        ])>
            <x-dynamic-component :component="'flux::icon.' . $icon" @class([
                'text-brand-500 dark:text-brand-400',
                'size-8' => ! $compact,
                'size-6' => $compact,
            ]) />
        </div>
    @endif

    @if ($heading)
        <flux:heading :size="$compact ? 'md' : 'lg'">{{ $heading }}</flux:heading>
    @endif

    @if ($text)
        <flux:text @class(['mt-2 max-w-sm', 'max-w-md text-sm' => $compact])>{{ $text }}</flux:text>
    @endif

    @isset($actions)
        <div @class(['mt-6' => ! $compact, 'mt-4' => $compact])>
            {{ $actions }}
        </div>
    @endisset
</div>
