<flux:modal name="save-deliverable" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">
                {{ $this->isEditing ? __('Edit Deliverable') : __('Add Deliverable') }}
            </flux:heading>
            <flux:text class="mt-2">
                {{ $this->isEditing
    ? __('Update deliverable details or manage attached files.')
    : __('Create a deliverable and link it to a milestone.') }}
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

        @php $currentType = $this->currentType; @endphp

        @if ($this->showFileUploader)
            <livewire:ui.file-uploader wire:key="file-uploader-{{ $fileUploaderKey }}" :state="$fileUploaderState"
                :max-files="$this->deliverableMaxFiles" :can-upload="$this->canUploadDeliverableFile"
                :description="$this->isEditing
                ? __('Add or remove files before saving.')
                : __('Attach one or more files now or add them later before submitting for review.')"
                :locked-message="__('File uploads are locked while this deliverable is in review or approved.')"
                :empty-text="__('Choose one or more files to attach to this deliverable.')" />
        @endif

        @if ($currentType?->isLink())
            <flux:field>
                <flux:label>{{ __('URL') }}</flux:label>
                <flux:input type="url" wire:model="link" placeholder="https://example.com/file.pdf" />
                <flux:error name="link" />
            </flux:field>
        @endif

        @if ($currentType?->isTextBased())
            <flux:field>
                <flux:label>{{ __('Content') }}</flux:label>
                <flux:textarea wire:model="content" rows="6" />
                <flux:error name="content" />
            </flux:field>
        @endif

        <x-ui.modal-footer>
            <flux:modal.close>
                <x-ui.button variant="secondary" class="!w-auto">{{ __('Cancel') }}</x-ui.button>
            </flux:modal.close>
            <x-ui.button wire:click="save" class="!w-auto">
                {{ $this->isEditing ? __('Save changes') : __('Create deliverable') }}
            </x-ui.button>
        </x-ui.modal-footer>
    </div>
</flux:modal>