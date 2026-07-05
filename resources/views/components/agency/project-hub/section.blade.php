@props(['heading', 'body' => true, 'flush' => false])

<section {{ $attributes->class(['handoff-panel']) }}>
    <header class="project-hub__section-header project-hub__section-header--stacked">
        <div class="min-w-0 flex-1 space-y-1">
            <flux:heading size="sm">{{ $heading }}</flux:heading>
            @isset($description)
                <flux:text class="text-sm text-brand-700/70 dark:text-brand-200/70">
                    {{ $description }}
                </flux:text>
            @endisset
        </div>
        @isset($actions)
            <div class="w-full shrink-0 sm:w-auto">
                {{ $actions }}
            </div>
        @endisset
    </header>

    <div @class([
        'handoff-panel__body' => $body && !$flush,
        'handoff-panel__body--flush' => $body && $flush,
    ])>
        {{ $slot }}
    </div>
</section>