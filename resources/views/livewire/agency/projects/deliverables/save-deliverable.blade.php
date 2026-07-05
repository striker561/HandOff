<flux:modal name="save-deliverable" flyout variant="floating" class="md:w-lg">
    <div class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <flux:heading size="lg">
                    @if ($readOnly)
                        {{ __('View Deliverable') }}
                    @elseif ($this->isEditing)
                        {{ __('Edit Deliverable') }}
                    @else
                        {{ __('Add Deliverable') }}
                    @endif
                </flux:heading>
                <flux:text class="mt-2">
                    @if ($readOnly)
                        {{ __('Submitted for client review. You can view what was sent, but it cannot be edited — like an email that has already gone out.') }}
                    @elseif ($this->isEditing)
                        {{ __('Update deliverable details or manage attached files.') }}
                    @else
                        {{ __('Create a deliverable and link it to a milestone.') }}
                    @endif
                </flux:text>
            </div>

            @if ($readOnly && $statusLabel !== '')
                <flux:badge :color="$statusBadgeColor" size="sm" class="shrink-0">
                    {{ $statusLabel }}
                </flux:badge>
            @endif
        </div>

        @if ($readOnly)
            <flux:callout icon="lock-closed">
                {{ __('This deliverable is locked. If the wrong file was sent, contact your client — the record here stays as submitted.') }}
            </flux:callout>
        @endif

        <flux:field>
            <flux:label>{{ __('Name') }}</flux:label>
            <flux:input wire:model="name" placeholder="{{ __('Deliverable name') }}" :readonly="$readOnly" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Milestone') }}</flux:label>
            @if ($readOnly)
                <flux:input readonly
                    :value="collect($this->milestones)->firstWhere('unique_id', $milestone_unique_id)?->name ?? '—'" />
            @else
                <flux:select wire:model="milestone_unique_id">
                    <flux:select.option value="">{{ __('Select a milestone') }}</flux:select.option>
                    @foreach ($this->milestones as $milestone)
                        <flux:select.option value="{{ $milestone->unique_id }}">{{ $milestone->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
            <flux:error name="milestone_unique_id" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Type') }}</flux:label>
            @if ($readOnly)
                <flux:input readonly :value="$this->currentType?->label() ?? '—'" />
            @else
                <flux:select wire:model.live="type">
                    @foreach ($this->deliverableTypes as $deliverableType)
                        <flux:select.option value="{{ $deliverableType->value }}">{{ $deliverableType->label() }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            @endif
            <flux:error name="type" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Description') }}</flux:label>
            <flux:textarea wire:model="description" rows="3" :readonly="$readOnly" />
            <flux:error name="description" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Due date') }}</flux:label>
            <flux:input type="date" wire:model="due_date" :readonly="$readOnly" />
            <flux:error name="due_date" />
        </flux:field>

        @php $currentType = $this->currentType; @endphp

        @if ($currentType?->isFileBased())
            <div wire:key="deliverable-field-files-{{ $type }}"
                class="space-y-6 rounded-lg border border-zinc-200 p-5 sm:p-6 dark:border-zinc-700">
                <div class="space-y-1">
                    <flux:heading size="sm">{{ __('Files') }}</flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        @if ($readOnly)
                            {{ __('Files submitted with this deliverable.') }}
                        @elseif ($this->isEditing)
                            {{ __('Add or remove files before saving.') }}
                        @else
                            {{ __('Attach one or more files now or add them later before submitting for review.') }}
                        @endif
                    </flux:text>
                </div>

                @if (!$readOnly && $this->canUploadDeliverableFile)
                        <div class="space-y-3 rounded-lg border border-dashed border-zinc-300 bg-zinc-50/50 p-4 dark:border-zinc-600 dark:bg-zinc-900/30"
                            x-data="{ progress: 0 }" x-on:livewire-upload-start="progress = 0"
                            x-on:livewire-upload-finish="progress = 0" x-on:livewire-upload-error="progress = 0"
                            x-on:livewire-upload-cancel="progress = 0"
                            x-on:livewire-upload-progress="progress = $event.detail.progress">
                            <div class="space-y-2">
                                <label class="handoff-label">{{ __('Upload files') }}</label>
                                <input type="file" wire:model="pendingDeliverableFiles" multiple @class([
                                    'block w-full text-sm text-zinc-500 file:mr-4 file:rounded-md file:border-0 file:bg-brand-500 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-zinc-400',
                                    'rounded-md border border-red-500' => $errors->has('pendingDeliverableFiles') || $errors->has('pendingDeliverableFiles.*'),
                                ]) />
                            </div>

                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Up to :count files, :limit each.', [
                        'count' => $this->deliverableMaxFiles,
                        'limit' => \App\Livewire\Ui\FileUploader::formatKilobytesLabel((int) config('handoff.uploads.max_kilobytes', 102400)),
                    ]) }}
                            </flux:text>

                            @if ($errors->has('pendingDeliverableFiles') || $errors->has('pendingDeliverableFiles.*'))
                                <flux:callout variant="danger" icon="x-circle">
                                    {{ $errors->first('pendingDeliverableFiles') ?: $errors->first('pendingDeliverableFiles.*') }}
                                </flux:callout>
                            @endif

                            <div wire:loading wire:target="pendingDeliverableFiles" class="space-y-2">
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Uploading files…') }}
                                </flux:text>
                                <progress class="h-2 w-full overflow-hidden rounded-full" max="100"
                                    x-bind:value="progress"></progress>
                            </div>
                        </div>
                @elseif (!$readOnly)
                    <flux:callout icon="lock-closed">
                        {{ __('File uploads are locked while this deliverable is in review or approved.') }}
                    </flux:callout>
                @endif

                @if ($pendingDeliverableFiles !== [] && $this->canUploadDeliverableFile && !$readOnly)
                    <div class="space-y-4 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                        <flux:heading size="sm">{{ __('New uploads') }}</flux:heading>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @foreach ($pendingDeliverableFiles as $index => $pendingFile)
                                @if ($pendingFile instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                    <article wire:key="pending-deliverable-{{ $pendingFile->getFilename() }}"
                                        class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                        @if (str_starts_with((string) $pendingFile->getMimeType(), 'image/'))
                                            <img src="{{ $pendingFile->temporaryUrl() }}" alt="{{ __('File preview') }}"
                                                class="mb-4 h-32 w-full rounded-md object-cover">
                                        @else
                                            <div class="mb-4 flex h-32 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800">
                                                <flux:icon.document class="size-10 text-zinc-400" />
                                            </div>
                                        @endif

                                        <div class="flex items-center justify-between gap-3">
                                            <flux:text class="truncate text-xs">{{ $pendingFile->getClientOriginalName() }}</flux:text>
                                            <flux:button type="button" variant="ghost" size="sm"
                                                wire:click="removePendingDeliverableFile({{ $index }})">
                                                {{ __('Remove') }}
                                            </flux:button>
                                        </div>
                                    </article>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($readOnly && $fileUploaderState['existing'] !== [])
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($fileUploaderState['existing'] as $index => $item)
                            <article wire:key="readonly-file-{{ $item['id'] ?? $index }}"
                                class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                @if ($item['is_image'] ?? false)
                                    <a href="{{ $item['preview_url'] }}" target="_blank" rel="noopener noreferrer"
                                        class="mb-4 block overflow-hidden rounded-md">
                                        <img src="{{ $item['preview_url'] }}" alt="{{ $item['label'] }}"
                                            class="h-32 w-full object-cover transition hover:opacity-90">
                                    </a>
                                @else
                                    <div class="mb-4 flex h-32 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon.document class="size-10 text-zinc-400" />
                                    </div>
                                @endif

                                <div class="space-y-2">
                                    <flux:text class="truncate font-medium">{{ $item['label'] }}</flux:text>
                                    @if (isset($item['size']))
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                            @if ($item['size'] >= 1048576)
                                                {{ number_format($item['size'] / 1048576, 1) }} MB
                                            @else
                                                {{ number_format($item['size'] / 1024, 1) }} KB
                                            @endif
                                        </flux:text>
                                    @endif
                                    <a href="{{ $item['preview_url'] }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                        <flux:icon.arrow-top-right-on-square variant="mini" class="size-4" />
                                        {{ __('Open in new tab') }}
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @elseif (!$readOnly && $fileUploaderState['existing'] !== [])
                    <livewire:ui.file-uploader wire:key="file-uploader-{{ $fileUploaderKey }}" hide-upload-input
                        :state="$fileUploaderState" :max-files="$this->deliverableMaxFiles"
                        :can-upload="$this->canUploadDeliverableFile" :heading="null" :description="null" />
                @elseif ($readOnly)
                    <x-ui.empty-state compact icon="document" :heading="__('No files attached')" :text="__('This deliverable was submitted without any files.')" />
                @elseif ($pendingDeliverableFiles === [] && $this->canUploadDeliverableFile)
                    <x-ui.empty-state compact icon="document-arrow-up" :heading="__('No files selected')" :text="__('Choose one or more files to attach to this deliverable.')" />
                @endif
            </div>
        @endif

        @if ($currentType?->isLink())
            <flux:field wire:key="deliverable-field-link-{{ $type }}">
                <flux:label>{{ __('URL') }}</flux:label>
                @if ($readOnly && $link)
                    <flux:text class="mt-1">
                        <a href="{{ $link }}" target="_blank" rel="noopener noreferrer"
                            class="text-brand-600 dark:text-brand-400 hover:underline">{{ $link }}</a>
                    </flux:text>
                @else
                    <flux:input type="url" wire:model="link" placeholder="https://example.com/file.pdf" :readonly="$readOnly" />
                @endif
                <flux:error name="link" />
            </flux:field>
        @endif

        @if ($currentType?->isTextBased())
            <flux:field wire:key="deliverable-field-content-{{ $type }}">
                <flux:label>{{ __('Content') }}</flux:label>
                <flux:textarea wire:model="content" rows="6" :readonly="$readOnly" />
                <flux:error name="content" />
            </flux:field>
        @endif

        <x-ui.modal-footer>
            <flux:modal.close>
                <x-ui.button variant="secondary" class="!w-auto">
                    {{ $readOnly ? __('Close') : __('Cancel') }}
                </x-ui.button>
            </flux:modal.close>
            @unless ($readOnly)
                <x-ui.button wire:click="save" class="!w-auto">
                    {{ $this->isEditing ? __('Save changes') : __('Create deliverable') }}
                </x-ui.button>
            @endunless
        </x-ui.modal-footer>
    </div>
</flux:modal>