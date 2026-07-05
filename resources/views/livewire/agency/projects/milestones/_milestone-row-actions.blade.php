@props(['milestone', 'mobile' => false])

@if ($mobile)
    <x-ui.button icon="pencil-square" wire:click="editMilestone('{{ $milestone->unique_id }}')">
        {{ __('Edit milestone') }}
    </x-ui.button>
@else
    <x-ui.button icon="pencil-square" icon-only wire:click="editMilestone('{{ $milestone->unique_id }}')">
        {{ __('Edit') }}
    </x-ui.button>
@endif