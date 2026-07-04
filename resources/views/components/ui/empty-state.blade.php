@props([
    'icon' => null,
    'heading' => null,
    'text' => null,
])

<div class="flex flex-col items-center justify-center py-24 text-center">
    @if ($icon)
        <div class="mb-6 flex size-16 items-center justify-center rounded-2xl bg-brand-100 dark:bg-brand-900">
            <x-dynamic-component :component="'flux::icon.' . $icon" class="size-8 text-brand-500 dark:text-brand-400" />
        </div>
    @endif

    @if ($heading)
        <flux:heading size="lg">{{ $heading }}</flux:heading>
    @endif

    @if ($text)
        <flux:text class="mt-2 max-w-sm">{{ $text }}</flux:text>
    @endif

    @isset($actions)
        <div class="mt-6">
            {{ $actions }}
        </div>
    @endisset
</div>
