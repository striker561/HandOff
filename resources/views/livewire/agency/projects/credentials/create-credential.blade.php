<flux:modal name="create-credential" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Add Credential') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Store encrypted credentials for this project. Passwords are never shown in lists.') }}
            </flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Name') }}</flux:label>
            <flux:input wire:model="name" placeholder="{{ __('Credential name') }}" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Type') }}</flux:label>
            <flux:select wire:model="type">
                @foreach ($this->credentialTypes as $credentialType)
                    <flux:select.option value="{{ $credentialType->value }}">{{ $credentialType->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="type" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Username') }}</flux:label>
            <flux:input wire:model="username" autocomplete="off" />
            <flux:error name="username" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Password') }}</flux:label>
            <flux:input type="password" wire:model="password" autocomplete="new-password" />
            <flux:error name="password" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('URL') }}</flux:label>
            <flux:input type="url" wire:model="url" placeholder="https://" />
            <flux:error name="url" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Notes') }}</flux:label>
            <flux:textarea wire:model="notes" rows="3" />
            <flux:error name="notes" />
        </flux:field>

        <x-ui.modal-footer>
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="create" variant="primary">{{ __('Save credential') }}</flux:button>
        </x-ui.modal-footer>
    </div>
</flux:modal>