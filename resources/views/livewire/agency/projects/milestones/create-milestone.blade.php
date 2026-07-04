<flux:modal name="create-milestone" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Add Milestone') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Create a project phase. Deliverables can be linked to this milestone later.') }}
            </flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Name') }}</flux:label>
            <flux:input wire:model="name" placeholder="{{ __('Phase name') }}" wire:keydown.enter="create" />
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
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="create" variant="primary">{{ __('Create milestone') }}</flux:button>
        </x-ui.modal-footer>
    </div>
</flux:modal>