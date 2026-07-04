@props([
    'wireClick' => null,
    'name' => null,
])

<button type="button" wire:click="{{ $wireClick }}"
    {{ $attributes->class(['handoff-btn', 'handoff-btn-primary', 'handoff-btn-action', 'px-3']) }}
    aria-label="{{ __('View :name', ['name' => $name]) }}">
    <flux:icon.eye variant="mini" class="size-4 shrink-0" />
    <span class="sr-only">{{ __('View details') }}</span>
</button>
