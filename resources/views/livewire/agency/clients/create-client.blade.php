<flux:modal name="create-client" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Create Client') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Invite a new client to your agency workspace. They will receive an email to set up their account.') }}
            </flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Name') }}</flux:label>
            <flux:input wire:model="name" placeholder="{{ __('Full name') }}" wire:keydown.enter="create" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Email') }}</flux:label>
            <flux:input type="email" wire:model="email" placeholder="{{ __('client@example.com') }}"
                wire:keydown.enter="create" />
            <flux:error name="email" />
        </flux:field>

        <x-ui.modal-footer>
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="create" variant="primary">{{ __('Send Invitation') }}</flux:button>
        </x-ui.modal-footer>
    </div>
</flux:modal>