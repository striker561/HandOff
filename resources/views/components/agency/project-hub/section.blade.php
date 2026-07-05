@props(['heading', 'body' => true])

<section {{ $attributes->class(['handoff-panel']) }}>
    <header class="project-hub__section-header">
        <flux:heading size="sm">{{ $heading }}</flux:heading>
        @isset($actions)
            {{ $actions }}
        @endisset
    </header>

    <div @class(['handoff-panel__body' => $body])>
        {{ $slot }}
    </div>
</section>
