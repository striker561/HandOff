@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="HandOff" {{ $attributes }}>
        <x-slot name="logo">
            <x-ui.logo-mark size="sm" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="HandOff" {{ $attributes }}>
        <x-slot name="logo">
            <x-ui.logo-mark size="sm" />
        </x-slot>
    </flux:brand>
@endif
