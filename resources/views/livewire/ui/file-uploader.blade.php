@php
    $existingItems = $state['existing'] ?? [];
    $pendingItems = $state['pending'] ?? [];
@endphp

<div class="space-y-6 rounded-lg border border-zinc-200 p-5 sm:p-6 dark:border-zinc-700">
    @if ($this->uploadErrorMessage !== '')
        <flux:callout variant="danger" icon="x-circle" :heading="$this->uploadErrorMessage" />
    @endif

    @if ($heading || !$hideUploadInput)
        <div class="space-y-1">
            <flux:heading size="sm">{{ $heading ?? __('Files') }}</flux:heading>
            @if ($description)
                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $description }}</flux:text>
            @endif
        </div>
    @endif

    @if ($canUpload && !$hideUploadInput)
        <div class="space-y-3 rounded-lg border border-dashed border-zinc-300 bg-zinc-50/50 p-4 dark:border-zinc-600 dark:bg-zinc-900/30"
            x-data="{ progress: 0 }" x-on:livewire-upload-start="progress = 0" x-on:livewire-upload-finish="progress = 0"
            x-on:livewire-upload-error="progress = 0" x-on:livewire-upload-cancel="progress = 0"
            x-on:livewire-upload-progress="progress = $event.detail.progress">
            <div class="space-y-2">
                <label class="handoff-label">{{ $uploadLabel ?? __('Upload files') }}</label>
                <input type="file" wire:model="state.pending" multiple @class([
                    'block w-full text-sm text-zinc-500 file:mr-4 file:rounded-md file:border-0 file:bg-brand-500 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-zinc-400',
                    'rounded-md border border-red-500' => $errors->has('state.pending') || $errors->has('state.pending.*'),
                ]) />
            </div>

            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('Up to :count files, :limit each.', ['count' => $maxFiles, 'limit' => $this->uploadLimitLabel]) }}
            </flux:text>

            <div wire:loading wire:target="state.pending" class="space-y-2">
                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Uploading files…') }}
                </flux:text>
                <progress class="h-2 w-full overflow-hidden rounded-full" max="100" x-bind:value="progress"></progress>
            </div>
        </div>
    @elseif ($lockedMessage)
        <flux:callout icon="lock-closed">{{ $lockedMessage }}</flux:callout>
    @endif

    @if ($existingItems !== [])
        <div class="space-y-4 border-t border-zinc-200 pt-6 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('Attached files') }}</flux:heading>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ($existingItems as $index => $item)
                    <article wire:key="existing-item-{{ $item['id'] ?? $index }}"
                        class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-md bg-brand-100 dark:bg-brand-900">
                                <flux:icon.document class="size-5 text-brand-600 dark:text-brand-400" />
                            </div>
                            <div class="min-w-0 flex-1 space-y-1">
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
                            </div>
                            @if ($canUpload)
                                <flux:button type="button" variant="ghost" size="sm" wire:click="removeExisting({{ $index }})">
                                    {{ __('Remove') }}
                                </flux:button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif

    @if ($pendingItems !== [] && $canUpload && !$hideUploadInput)
        <div class="space-y-4 border-t border-zinc-200 pt-6 dark:border-zinc-700">
            <flux:heading size="sm">{{ __('New uploads') }}</flux:heading>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ($pendingItems as $index => $pendingFile)
                    @if ($pendingFile)
                        <article wire:key="pending-item-{{ $index }}"
                            class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            @if (method_exists($pendingFile, 'temporaryUrl') && str_starts_with((string) $pendingFile->getMimeType(), 'image/'))
                                <img src="{{ $pendingFile->temporaryUrl() }}" alt="{{ __('File preview') }}"
                                    class="mb-4 h-32 w-full rounded-md object-cover">
                            @else
                                <div class="mb-4 flex h-32 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon.document class="size-10 text-zinc-400" />
                                </div>
                            @endif

                            <div class="flex items-center justify-between gap-3">
                                <flux:text class="truncate text-xs">{{ $pendingFile->getClientOriginalName() }}</flux:text>
                                <flux:button type="button" variant="ghost" size="sm" wire:click="removePending({{ $index }})">
                                    {{ __('Remove') }}
                                </flux:button>
                            </div>
                        </article>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if ($existingItems === [] && $pendingItems === [] && $canUpload && !$hideUploadInput)
        <x-ui.empty-state compact icon="document-arrow-up" :heading="$emptyHeading ?? __('No files selected')"
            :text="$emptyText ?? __('Choose one or more files to upload.')" />
    @endif
</div>