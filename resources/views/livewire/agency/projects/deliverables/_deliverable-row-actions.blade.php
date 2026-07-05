@props(['deliverable', 'mobile' => false])

@if ($deliverable->status->isAgencyEditable())
    <x-ui.button icon="pencil-square" :icon-only="! $mobile" wire:click="editDeliverable('{{ $deliverable->unique_id }}')">
        {{ __('Edit') }}
    </x-ui.button>
    <x-ui.button icon="paper-airplane" :icon-only="! $mobile"
        wire:click="confirmSubmitForReview('{{ $deliverable->unique_id }}')">
        {{ __('Submit for review') }}
    </x-ui.button>
    <x-ui.button icon="trash" :icon-only="! $mobile" wire:click="confirmDeleteDeliverable('{{ $deliverable->unique_id }}')">
        {{ __('Delete') }}
    </x-ui.button>
@else
    <x-ui.button icon="eye" :icon-only="! $mobile" wire:click="viewDeliverable('{{ $deliverable->unique_id }}')">
        {{ __('View') }}
    </x-ui.button>
@endif