@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="HandOff" {{ $attributes }}>
        <x-slot name="logo" class="handoff-clip flex aspect-square size-8 items-center justify-center bg-brand-700 text-white shadow-sm dark:bg-brand-600">
            <x-app-logo-icon class="size-5 text-white" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="HandOff" {{ $attributes }}>
        <x-slot name="logo" class="handoff-clip flex aspect-square size-8 items-center justify-center bg-brand-700 text-white shadow-sm dark:bg-brand-600">
            <x-app-logo-icon class="size-5 text-white" />
        </x-slot>
    </flux:brand>
@endif
