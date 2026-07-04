<flux:modal name="save-milestone" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $this->isEditing ? __('Edit Milestone') : __('Add Milestone') }}
            </flux:heading>
            <flux:text class="mt-2">
                {{ $this->isEditing
                    ? __('Update this project phase.')
                    : __('Create a project phase. Deliverables can be linked to this milestone later.') }}
            </flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Name') }}</flux:label>
            <flux:input wire:model="name" placeholder="{{ __('Phase name') }}" wire:keydown.enter="save" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Description') }}</flux:label>
            <flux:textarea wire:model="description" rows="3" />
            <flux:error name="description" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Due date') }}</flux:label>
            <flux:input type="date" wire:model="due_date" />
            <flux:error name="due_date" />
        </flux:field>

        <x-ui.modal-footer>
            <flux:modal.close>
                <x-ui.button variant="secondary" class="!w-auto">{{ __('Cancel') }}</x-ui.button>
            </flux:modal.close>
            <x-ui.button wire:click="save" class="!w-auto">
                {{ $this->isEditing ? __('Save changes') : __('Create milestone') }}
            </x-ui.button>
        </x-ui.modal-footer>
    </div>
</flux:modal>
