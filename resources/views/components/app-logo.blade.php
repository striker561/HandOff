@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="HandOff" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-brand-700 text-white shadow-sm ring-1 ring-brand-600/25">
            <x-app-logo-icon class="size-5 text-white" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="HandOff" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-brand-700 text-white shadow-sm ring-1 ring-brand-600/25">
            <x-app-logo-icon class="size-5 text-white" />
        </x-slot>
    </flux:brand>
@endif
