@props(['milestone', 'mobile' => false])

<x-ui.button icon="pencil-square" :icon-only="! $mobile" wire:click="editMilestone('{{ $milestone->unique_id }}')">
    {{ $mobile ? __('Edit milestone') : __('Edit') }}
</x-ui.button>

@if ($milestone->isDeletable())
    <x-ui.button icon="trash" :icon-only="! $mobile" wire:click="confirmDeleteMilestone('{{ $milestone->unique_id }}')">
        {{ __('Delete') }}
    </x-ui.button>
@endif