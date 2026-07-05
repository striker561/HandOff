<flux:modal name="save-credential" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $this->isEditing ? __('Edit Credential') : __('Add Credential') }}
            </flux:heading>
            <flux:text class="mt-2">
                {{ $this->isEditing
    ? __('Update credential details. Leave password blank to keep the current password.')
    : __('Store encrypted credentials for this project. Username, URL, notes, and password are encrypted at rest and only shown after reveal.') }}
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
            <flux:label>{{ $this->isEditing ? __('New password') : __('Password') }}</flux:label>
            <flux:input type="password" wire:model="password" autocomplete="new-password"
                :placeholder="$this->isEditing ? __('Leave blank to keep current') : null" />
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
                <x-ui.button variant="secondary" class="!w-auto">{{ __('Cancel') }}</x-ui.button>
            </flux:modal.close>
            <x-ui.button wire:click="save" class="!w-auto">
                {{ $this->isEditing ? __('Save changes') : __('Save credential') }}
            </x-ui.button>
        </x-ui.modal-footer>
    </div>
</flux:modal>