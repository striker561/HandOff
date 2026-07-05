@props([
    'wireClick' => null,
    'name' => null,
])

<x-ui.button type="button" wire:click="{{ $wireClick }}" icon="eye" icon-only
    :aria-label="__('View :name', ['name' => $name])" {{ $attributes }}>
    {{ __('View details') }}
</x-ui.button>
