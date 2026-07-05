<flux:modal name="save-meeting" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $this->isEditing ? __('Edit Meeting') : __('Schedule Meeting') }}
            </flux:heading>
            <flux:text class="mt-2">
                {{ $this->isEditing
                    ? __('Update meeting details for this project.')
                    : __('Schedule a meeting for this project. Optionally link it to a deliverable.') }}
            </flux:text>
        </div>

        <flux:field>
            <flux:label>{{ __('Title') }}</flux:label>
            <flux:input wire:model="title" placeholder="{{ __('Meeting title') }}" />
            <flux:error name="title" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Description') }}</flux:label>
            <flux:textarea wire:model="description" rows="3" />
            <flux:error name="description" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Date & time') }}</flux:label>
            <flux:input type="datetime-local" wire:model="scheduled_at" />
            <flux:error name="scheduled_at" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Duration (minutes)') }}</flux:label>
            <flux:input type="number" wire:model="duration_minutes" min="15" max="480" step="15" />
            <flux:error name="duration_minutes" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Location') }}</flux:label>
            <flux:select wire:model="location">
                @foreach ($this->meetingLocations as $meetingLocation)
                    <flux:select.option value="{{ $meetingLocation->value }}">{{ $meetingLocation->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="location" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Deliverable (optional)') }}</flux:label>
            <flux:select wire:model="deliverable_unique_id">
                <flux:select.option value="">{{ __('None') }}</flux:select.option>
                @foreach ($this->deliverables as $deliverable)
                    <flux:select.option value="{{ $deliverable->unique_id }}">{{ $deliverable->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="deliverable_unique_id" />
        </flux:field>

        <x-ui.modal-footer>
            <flux:modal.close>
                <x-ui.button variant="secondary" class="!w-auto">{{ __('Cancel') }}</x-ui.button>
            </flux:modal.close>
            <x-ui.button wire:click="save" class="!w-auto">
                {{ $this->isEditing ? __('Save changes') : __('Schedule meeting') }}
            </x-ui.button>
        </x-ui.modal-footer>
    </div>
</flux:modal>
