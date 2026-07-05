<flux:modal name="view-client" flyout variant="floating" class="md:w-lg" @close="close">
    @if ($uniqueId)
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Client details') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Account information for this client.') }}
                </flux:text>
            </div>

            <dl class="space-y-4">
                <div>
                    <flux:text class="text-sm font-medium">{{ __('Name') }}</flux:text>
                    <flux:text class="mt-1">{{ $name }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm font-medium">{{ __('Email') }}</flux:text>
                    <flux:text class="mt-1">{{ $email }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm font-medium">{{ __('Status') }}</flux:text>
                    <flux:text class="mt-1">{{ $status }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm font-medium">{{ __('Joined') }}</flux:text>
                    <flux:text class="mt-1">{{ $joined }}</flux:text>
                </div>
            </dl>

            @if ($isInvited)
                <flux:field>
                    <flux:error name="invitation" />
                </flux:field>
            @endif

            <x-ui.modal-footer>
                <x-ui.button variant="secondary" class="!w-auto" wire:click="close">{{ __('Close') }}</x-ui.button>
                @if ($isInvited)
                    <x-ui.button wire:click="resendInvitation" variant="primary" icon="arrow-path">
                        {{ __('Resend invitation') }}
                    </x-ui.button>
                @endif
            </x-ui.modal-footer>
        </div>
    @endif
</flux:modal>
