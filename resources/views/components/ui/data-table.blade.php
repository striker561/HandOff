@props([
    'paginate' => null,
    'panel' => true,
    'flush' => false,
])

<div {{ $attributes->class([
    'handoff-data-table min-w-0',
    'my-5' => ! $flush,
    'my-0' => $flush,
    'handoff-data-table--flush' => $flush,
]) }}>
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
