<div>
    <flux:heading class="sr-only">{{ __('Security settings') }}</flux:heading>

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form wire:submit="updatePassword" class="space-y-5">
            <x-ui.input wire:model="current_password" name="current_password" :label="__('Current password')"
                type="password" viewable required autocomplete="current-password" />

            <x-ui.input wire:model="password" name="password" :label="__('New password')" type="password" viewable
                required autocomplete="new-password" />

            <x-ui.input wire:model="password_confirmation" name="password_confirmation" :label="__('Confirm password')"
                type="password" viewable required autocomplete="new-password" />

            <div class="flex items-center justify-center gap-4 pt-2">
                <x-ui.button type="submit" icon="check" class="!w-auto" data-test="update-password-button">
                    {{ __('Save') }}
                </x-ui.button>
            </div>
        </form>

        @if ($canManageTwoFactor)
            <section class="settings-layout__divider">
                <flux:heading>{{ __('Two-factor authentication') }}</flux:heading>
                <flux:subheading>{{ __('Manage your two-factor authentication settings') }}</flux:subheading>

                <div class="mt-6 flex w-full flex-col items-center space-y-6 text-center text-sm" wire:cloak>
                    @if ($twoFactorEnabled)
                        <div class="space-y-4">
                            <flux:text>
                                {{ __('You will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                            </flux:text>

                            <div class="flex justify-center">
                                <x-ui.button type="button" variant="outline" wire:click="disableTwoFactor" class="!w-auto">
                                    {{ __('Disable 2FA') }}
                                </x-ui.button>
                            </div>

                            <livewire:settings.two-factor.recovery-codes />
                        </div>
                    @else
                        <div class="space-y-4">
                            <flux:text variant="subtle">
                                {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                            </flux:text>

                            <flux:modal.trigger name="two-factor-setup-modal">
                                <x-ui.button type="button" wire:click="$dispatch('start-two-factor-setup')" class="!w-auto">
                                    {{ __('Enable 2FA') }}
                                </x-ui.button>
                            </flux:modal.trigger>

                            <livewire:settings.two-factor-setup-modal :requires-confirmation="true" />
                        </div>
                    @endif
                </div>
            </section>
        @endif
    </x-settings.layout>
</div>