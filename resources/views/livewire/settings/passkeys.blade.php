<div>
    <flux:heading class="sr-only">{{ __('Passkey settings') }}</flux:heading>

    <x-settings.layout :heading="__('Passkeys')" :subheading="__('Manage your passkeys for passwordless sign-in')">
        <div class="flex w-full flex-col space-y-6 text-sm" wire:cloak>
            <div class="w-full overflow-hidden rounded-xl border border-brand-200/60 dark:border-brand-800/50">
                @forelse ($passkeys as $passkey)
                    <div
                        class="flex items-center justify-between p-4 {{ !$loop->last ? 'border-b border-brand-200/60 dark:border-brand-800/50' : '' }}">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-900">
                                <flux:icon.key class="size-5 text-brand-600 dark:text-brand-300" />
                            </div>
                            <div class="space-y-1 text-left">
                                <div class="flex items-center gap-2.5">
                                    <p class="font-medium tracking-tight">{{ $passkey['name'] }}</p>
                                    @if ($passkey['authenticator'])
                                        <flux:badge size="sm">{{ $passkey['authenticator'] }}</flux:badge>
                                    @endif
                                </div>
                                <p class="text-xs text-brand-600/70 dark:text-brand-300/70">
                                    {{ __('Added :time', ['time' => $passkey['created_at_diff']]) }}
                                    @if ($passkey['last_used_at_diff'])
                                        <span class="mx-1 opacity-50">/</span>
                                        {{ __('Last used :time', ['time' => $passkey['last_used_at_diff']]) }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <flux:button variant="ghost" size="sm" icon="trash" icon:variant="outline"
                            wire:click="confirmDelete({{ $passkey['id'] }})"
                            class="text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/50" />
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div
                            class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-brand-100 dark:bg-brand-900">
                            <flux:icon.key class="size-7 text-brand-500 dark:text-brand-400" />
                        </div>
                        <p class="font-medium">{{ __('No passkeys yet') }}</p>
                        <flux:text class="mt-1">
                            {{ __('Add a passkey to sign in without a password') }}
                        </flux:text>
                    </div>
                @endforelse
            </div>

            <x-passkey-registration />
        </div>
    </x-settings.layout>

    <x-ui.confirm-modal name="delete-passkey-modal" class="max-w-md md:min-w-md" :heading="__('Remove passkey')"
        :description="__('Are you sure you want to remove the passkey \":name\"? You will no longer be able to use it to sign in.', ['name' => $deletingPasskeyName])" :confirm-label="__('Remove passkey')"
        confirm-action="deletePasskey" cancel-action="closeDeleteModal" />
</div>