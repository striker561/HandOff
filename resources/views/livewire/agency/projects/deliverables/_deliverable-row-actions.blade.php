@props(['deliverable', 'mobile' => false])

@if ($deliverable->status !== \App\Enums\Deliverable\DeliverableStatus::APPROVED)
    <x-ui.button icon="pencil-square" :icon-only="! $mobile" wire:click="editDeliverable('{{ $deliverable->unique_id }}')">
        {{ __('Edit') }}
    </x-ui.button>
    <x-ui.button icon="check" :icon-only="! $mobile" wire:click="approve('{{ $deliverable->unique_id }}')">
        {{ __('Approve') }}
    </x-ui.button>
@endif
@if ($deliverable->status !== \App\Enums\Deliverable\DeliverableStatus::REJECTED)
    <x-ui.button icon="x-mark" :icon-only="! $mobile" wire:click="reject('{{ $deliverable->unique_id }}')">
        {{ __('Reject') }}
    </x-ui.button>
@endif