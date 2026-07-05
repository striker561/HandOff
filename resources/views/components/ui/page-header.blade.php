@props([
    'heading' => null,
    'subheading' => null,
])

<header {{ $attributes->class(['handoff-page__header']) }}>
    <div class="space-y-1">
        <flux:heading size="xl">{{ $heading }}</flux:heading>
        @if ($subheading)
            <flux:text class="handoff-page__lede">{{ $subheading }}</flux:text>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{ $actions }}
        </div>
    @endisset
</header>
