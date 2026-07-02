<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset your password')" :description="__('Enter the email on your account and we\'ll send a reset link.')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
            @csrf

            <x-handoff-input name="email" :label="__('Email address')" type="email" required autofocus
                placeholder="you@agency.com" />

            <x-handoff-button type="submit" icon="envelope">
                {{ __('Send reset link') }}
            </x-handoff-button>
        </form>

        <p class="text-center text-sm text-brand-700/60 dark:text-brand-300/60">
            {{ __('Remember your password?') }}
            <a class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                href="{{ route('login') }}" wire:navigate>{{ __('Back to log in') }}</a>
        </p>
    </div>
</x-layouts::auth>