<flux:modal name="edit-credential" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Edit Credential') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Update credential details. Leave password blank to keep the current password.') }}
            </flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Name') }}</flux:label>
            <flux:input wire:model="name" />
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
            <flux:label>{{ __('New password') }}</flux:label>
            <flux:input type="password" wire:model="password" autocomplete="new-password"
                placeholder="{{ __('Leave blank to keep current') }}" />
            <flux:error name="password" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('URL') }}</flux:label>
            <flux:input type="url" wire:model="url" />
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
            <flux:button wire:click="save" variant="primary">{{ __('Save changes') }}</flux:button>
        </x-ui.modal-footer>
    </div>
</flux:modal>