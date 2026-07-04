@props([
    'paginate' => null,
])

<div {{ $attributes->class(['handoff-data-table my-5 min-w-0']) }}>
    <div class="handoff-panel overflow-hidden">
        <flux:table :paginate="$paginate">
            {{ $slot }}
        </flux:table>
    </div>
</div>
ƒ
