@props(['deliverable', 'mobile' => false])

@if ($deliverable->status->isAgencyEditable())
    <x-ui.button icon="pencil-square" :icon-only="! $mobile" wire:click="editDeliverable('{{ $deliverable->unique_id }}')">
        {{ __('Edit') }}
    </x-ui.button>
    <x-ui.button icon="paper-airplane" :icon-only="! $mobile" wire:click="submitForReview('{{ $deliverable->unique_id }}')">
        {{ __('Submit for review') }}
    </x-ui.button>
@endif