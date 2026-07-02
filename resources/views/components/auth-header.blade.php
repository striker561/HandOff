@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex w-full flex-col gap-1']) }}>
<flux:heading size="xl" class="text-balance">{{ $title }}</flux:heading>

    @if ($description)
        <flux:subheading class="text-balance text-brand-700/60 dark:text-brand-300/60">{{ $description }}
        </flux:subheading>
    @endif
</div>
