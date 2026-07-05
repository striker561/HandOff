<flux:modal name="view-credential" flyout variant="floating" class="md:w-lg" @close="close">
    @if ($uniqueId)
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <flux:heading size="lg">{{ $name }}</flux:heading>
                    <flux:text class="mt-2">
                        {{ __('Sensitive fields are encrypted at rest. Reveal to view username, URL, notes, and password.') }}
                    </flux:text>
                </div>

                <flux:badge :color="$typeBadgeColor" size="sm" class="shrink-0">
                    {{ $typeLabel }}
                </flux:badge>
            </div>

            @unless ($detailsRevealed)
                <flux:callout icon="lock-closed">
                    {{ __('Login details are hidden until you reveal them. Access is logged.') }}
                </flux:callout>

                <x-ui.button wire:click="revealDetails" icon="eye" class="!w-auto">
                    {{ __('Reveal details') }}
                </x-ui.button>
            @else
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
                                    class="text-brand-600 dark:text-brand-400 hover:underline">{{ $url }}</a>
                            @else
                                —
                            @endif
                        </flux:text>
                    </div>
                    <div class="sm:col-span-2">
                        <flux:text class="text-sm font-medium">{{ __('Password') }}</flux:text>
                        @if ($revealedPassword)
                            <div class="mt-2 flex items-center gap-2" x-data="{ password: @js($revealedPassword) }">
                                <flux:input readonly :value="$revealedPassword" class="font-mono" />
                                <x-ui.button x-on:click="navigator.clipboard.writeText(password)" icon="clipboard"
                                    class="!w-auto px-3 py-2">
                                    <span class="sr-only">{{ __('Copy') }}</span>
                                </x-ui.button>
                            </div>
                        @else
                            <flux:text class="mt-1">—</flux:text>
                        @endif
                    </div>
                    @if ($notes)
                        <div class="sm:col-span-2">
                            <flux:text class="text-sm font-medium">{{ __('Notes') }}</flux:text>
                            <flux:text class="mt-1">{{ $notes }}</flux:text>
                        </div>
                    @endif
                </dl>
            @endunless

            <x-ui.modal-footer>
                <x-ui.button wire:click="close" variant="secondary" class="!w-auto">{{ __('Close') }}</x-ui.button>
                <x-ui.button wire:click="edit" icon="pencil-square" class="!w-auto">
                    {{ __('Edit') }}
                </x-ui.button>
            </x-ui.modal-footer>
        </div>
    @endif
</flux:modal>
