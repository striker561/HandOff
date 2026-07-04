@props([
    'paginate' => null,
    'panel' => true,
])

<div {{ $attributes->class(['handoff-data-table my-5 min-w-0']) }}>
    @if ($panel)
        <div class="handoff-panel overflow-hidden">
            <flux:table :paginate="$paginate">
                {{ $slot }}
            </flux:table>
        </div>
    @else
        <flux:table :paginate="$paginate">
            {{ $slot }}
        </flux:table>
    @endif
</div>
