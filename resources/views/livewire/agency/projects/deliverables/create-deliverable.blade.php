<flux:modal name="create-deliverable" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Add Deliverable') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Create a deliverable and link it to a milestone.') }}
            </flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Name') }}</flux:label>
            <flux:input wire:model="name" placeholder="{{ __('Deliverable name') }}" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Milestone') }}</flux:label>
            <flux:select wire:model="milestone_unique_id">
                <flux:select.option value="">{{ __('Select a milestone') }}</flux:select.option>
                @foreach ($this->milestones as $milestone)
                    <flux:select.option value="{{ $milestone->unique_id }}">{{ $milestone->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="milestone_unique_id" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Type') }}</flux:label>
            <flux:select wire:model="type">
                @foreach ($this->deliverableTypes as $deliverableType)
                    <flux:select.option value="{{ $deliverableType->value }}">{{ $deliverableType->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="type" />
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

        <flux:field>
            <flux:label>{{ __('File') }}</flux:label>
            <flux:input type="file" wire:model="file" />
            <flux:error name="file" />
        </flux:field>

        <x-ui.modal-footer>
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="create" variant="primary">{{ __('Create deliverable') }}</flux:button>
        </x-ui.modal-footer>
    </div>
</flux:modal>