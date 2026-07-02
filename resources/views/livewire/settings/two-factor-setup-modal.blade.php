<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showVerificationStep = false;

    public bool $setupComplete = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    public function mount(bool $requiresConfirmation): void
    {
        $this->requiresConfirmation = $requiresConfirmation;
    }

    #[On('start-two-factor-setup')]
    public function startTwoFactorSetup(): void
    {
        $enableTwoFactorAuthentication = app(EnableTwoFactorAuthentication::class);
        $enableTwoFactorAuthentication(auth()->user());

        $this->loadSetupData();
    }

    private function loadSetupData(): void
    {
        $user = auth()->user()?->fresh();

        try {
            if (!$user || !$user->two_factor_secret) {
                throw new Exception('Two-factor setup secret is not available.');
            }

            $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');
            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;
            $this->resetErrorBag();
            return;
        }

        $this->closeModal();
        $this->dispatch('two-factor-enabled');
    }

    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();
        $confirmTwoFactorAuthentication(auth()->user(), $this->code);
        $this->setupComplete = true;
        $this->closeModal();
        $this->dispatch('two-factor-enabled');
    }

    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');
        $this->resetErrorBag();
    }

    public function closeModal(): void
    {
        $this->reset('code', 'manualSetupKey', 'qrCodeSvg', 'showVerificationStep', 'setupComplete');
        $this->resetErrorBag();
    }

    #[Computed]
    public function modalConfig(): array
    {
        if ($this->setupComplete) {
            return [
                'title' => __('Two-factor authentication enabled'),
                'description' => __('Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'),
                'buttonText' => __('Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify authentication code'),
                'description' => __('Enter the 6-digit code from your authenticator app.'),
                'buttonText' => __('Continue'),
            ];
        }

        return [
            'title' => __('Enable two-factor authentication'),
            'description' => __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'),
            'buttonText' => __('Continue'),
        ];
    }
}; ?>

<flux:modal name="two-factor-setup-modal" class="max-w-md md:min-w-md" @close="closeModal">
    <div class="space-y-6">
        @if ($showVerificationStep)
            <div class="flex flex-col items-center space-y-4">
                <div class="handoff-panel__mark">
                    <flux:icon.shield-check class="size-7" />
                </div>

                <div class="space-y-2 text-center">
                    <flux:heading size="lg">{{ $this->modalConfig['title'] }}</flux:heading>
                    <flux:text>{{ $this->modalConfig['description'] }}</flux:text>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex flex-col items-center space-y-3 justify-center" x-data
                    x-init="$nextTick(() => $el.querySelector('input')?.focus())">
                    <flux:otp name="code" wire:model="code" length="6" label="OTP Code" label:sr-only class="mx-auto" />
                </div>

                <div class="flex items-center gap-3">
                    <flux:button variant="outline" class="flex-1" wire:click="resetVerification">{{ __('Back') }}
                    </flux:button>
                    <flux:button variant="primary" class="flex-1" wire:click="confirmTwoFactor"
                        x-bind:disabled="$wire.code.length < 6">{{ __('Confirm') }}</flux:button>
                </div>
            </div>
        @else
            <div class="space-y-2 text-center">
                <flux:heading size="lg">{{ $this->modalConfig['title'] }}</flux:heading>
                <flux:text>{{ __('Scan this QR code with your authenticator app, then continue.') }}</flux:text>
            </div>

            @error('setupData')
                <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
            @enderror

            <div class="flex justify-center">
                <div class="handoff-panel__qr">
                    @empty($qrCodeSvg)
                        <div
                            class="absolute inset-0 flex items-center justify-center bg-brand-50 animate-pulse dark:bg-brand-900/50">
                            <flux:icon.loading />
                        </div>
                    @else
                        <div x-data class="flex h-full items-center justify-center p-4">
                            <div class="rounded bg-white p-3"
                                :style="($flux.appearance === 'dark' || ($flux.appearance === 'system' && $flux.dark)) ? 'filter: invert(1) brightness(1.5)' : ''">
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>
                    @endempty
                </div>
            </div>

            <flux:button :disabled="$errors->has('setupData')" variant="primary" class="w-full"
                wire:click="showVerificationIfNecessary">
                {{ $this->modalConfig['buttonText'] }}
            </flux:button>

            <div class="flex justify-center" x-data="{
                    copied: false,
                    async copy() {
                        try {
                            await navigator.clipboard.writeText(@js($manualSetupKey));
                            this.copied = true;
                            setTimeout(() => this.copied = false, 1500);
                        } catch (e) {
                            console.warn('Could not copy to clipboard');
                        }
                    },
                }">
                <flux:dropdown position="bottom" align="center">
                    <flux:button variant="ghost" size="sm" icon="key" :disabled="$errors->has('setupData')">
                        {{ __("Can't scan? Enter setup key manually") }}
                    </flux:button>

                    <flux:menu class="min-w-72 border-brand-200 p-4 dark:border-brand-700">
                        <flux:heading size="sm">{{ __('Setup key') }}</flux:heading>
                        <flux:text variant="subtle" class="mt-1 text-xs">
                            {{ __('Paste this key into your authenticator app if you cannot scan the QR code.') }}
                        </flux:text>

                        <div class="mt-3">
                            @empty($manualSetupKey)
                                <div class="flex items-center justify-center rounded-lg bg-brand-100 p-6 dark:bg-brand-900/50">
                                    <flux:icon.loading variant="mini" />
                                </div>
                            @else
                                <p class="handoff-setup-key">{{ $manualSetupKey }}</p>

                                <flux:button type="button" variant="outline" class="mt-3 w-full" icon="document-duplicate"
                                    x-on:click="copy()" x-bind:disabled="copied">
                                    <span x-show="!copied">{{ __('Copy setup key') }}</span>
                                    <span x-show="copied" x-cloak>{{ __('Copied!') }}</span>
                                </flux:button>
                            @endempty
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </div>
        @endif
    </div>
</flux:modal>