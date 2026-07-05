<flux:modal name="save-client" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Create Client') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Invite a new client to your workspace. They will receive an email to set up their account.') }}
            </flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Name') }}</flux:label>
            <flux:input wire:model="name" placeholder="{{ __('Full name') }}" wire:keydown.enter="save" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Email') }}</flux:label>
            <flux:input type="email" wire:model="email" placeholder="{{ __('client@example.com') }}"
                wire:keydown.enter="save" />
            <flux:error name="email" />
            <flux:error name="invitation" />
        </flux:field>

        <x-ui.modal-footer>
            <flux:modal.close>
                <x-ui.button variant="secondary" class="!w-auto">{{ __('Cancel') }}</x-ui.button>
            </flux:modal.close>
            <x-ui.button wire:click="save" class="!w-auto">{{ __('Send Invitation') }}</x-ui.button>
        </x-ui.modal-footer>
    </div>
</flux:modal>