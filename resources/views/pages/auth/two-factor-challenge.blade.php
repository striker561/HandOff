<x-layouts::auth :title="__('Two-factor authentication')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Two-factor authentication')" :description="__('Please confirm access to your account by entering the authentication code provided by your authenticator application.')" />

        <form method="POST" action="{{ route('two-factor.login') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input name="code" :label="__('Code')" type="text" inputmode="numeric" required autofocus
                autocomplete="one-time-code" placeholder="123456" />

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Login') }}
            </flux:button>
        </form>

        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-zinc-200 dark:border-zinc-700"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="px-2 text-zinc-500 dark:text-zinc-400 bg-white dark:bg-zinc-900">
                    {{ __('Or') }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('two-factor.login') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input name="recovery_code" :label="__('Recovery code')" type="text"
                placeholder="XXXX-XXXX-XXXX-XXXX" />

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Use recovery code') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>