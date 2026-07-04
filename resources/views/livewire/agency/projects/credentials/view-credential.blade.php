<flux:modal name="view-credential" flyout variant="floating" class="md:w-lg" @close="close">
    @if ($uniqueId)
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <flux:heading size="lg">{{ $name }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Credential details for this project.') }}</flux:text>
                </div>

                <flux:badge :color="$typeBadgeColor" size="sm" class="shrink-0">
                    {{ $typeLabel }}
                </flux:badge>
            </div>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:text class="text-sm font-medium">{{ __('Username') }}</flux:text>
                    <flux:text class="mt-1">{{ $username ?? '—' }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm font-medium">{{ __('URL') }}</flux:text>
                    <flux:text class="mt-1">
                        @if ($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                class="text-brand-600 hover:underline dark:text-brand-400">{{ $url }}</a>
                        @else
                            —
                        @endif
                    </flux:text>
                </div>
                <div class="sm:col-span-2">
                    <flux:text class="text-sm font-medium">{{ __('Password') }}</flux:text>
                    @if ($passwordRevealed && $revealedPassword)
                        <div class="mt-2 flex items-center gap-2">
                            <flux:input readonly :value="$revealedPassword" class="font-mono" />
                            <flux:button x-data x-on:click="navigator.clipboard.writeText(@js($revealedPassword))"
                                variant="ghost" size="sm" icon="clipboard" :tooltip="__('Copy')" />
                        </div>
                    @else
                        <div class="mt-2">
                            <flux:button wire:click="revealPassword" variant="filled" size="sm" icon="eye">
                                {{ __('Reveal password') }}
                            </flux:button>
                        </div>
                    @endif
                </div>
                @if ($notes)
                    <div class="sm:col-span-2">
                        <flux:text class="text-sm font-medium">{{ __('Notes') }}</flux:text>
                        <flux:text class="mt-1">{{ $notes }}</flux:text>
                    </div>
                @endif
            </dl>

            <x-ui.modal-footer>
                <flux:button wire:click="close" variant="filled">{{ __('Close') }}</flux:button>
                <flux:button wire:click="edit" variant="primary" icon="pencil-square">
                    {{ __('Edit') }}
                </flux:button>
            </x-ui.modal-footer>
        </div>
    @endif
</flux:modal>