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

        @php
            $statuses = \App\Enums\Milestone\MilestoneStatus::selectable();
            $currentStatus = \App\Enums\Milestone\MilestoneStatus::tryFrom($status);
        @endphp

        <flux:field>
            <flux:label>{{ __('Status') }}</flux:label>
            @if ($this->isStatusLocked && $currentStatus)
                <div class="space-y-2">
                    <flux:badge :color="$currentStatus->badgeColor()" size="sm">
                        {{ $currentStatus->label() }}
                    </flux:badge>
                    <flux:text variant="subtle" class="text-sm">
                        {{ __('Completed automatically when all deliverables in this milestone are approved.') }}
                    </flux:text>
                </div>
            @else
                <flux:select wire:model="status">
                    @foreach ($statuses as $milestoneStatus)
                        <flux:select.option value="{{ $milestoneStatus->value }}">
                            {{ $milestoneStatus->label() }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:text variant="subtle" class="mt-1 text-sm">
                    {{ __('When all deliverables are approved, the milestone completes automatically.') }}
                </flux:text>
                <flux:error name="status" />
            @endif
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