<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
            @csrf

            <x-ui.input name="email" :label="__('Email address')" type="email" :value="old('email')" required
                autofocus autocomplete="email" placeholder="you@agency.com" />

            <x-ui.input name="password" :label="__('Password')" viewable required autocomplete="current-password"
                :placeholder="__('Your password')" />

            <div class="flex items-center justify-between gap-4">
                <x-ui.checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

                @if (Route::has('password.request'))
                    <a class="shrink-0 text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                        href="{{ route('password.request') }}" wire:navigate>
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-ui.button type="submit" icon="arrow-right-start-on-rectangle">
                {{ __('Log in') }}
            </x-ui.button>
        </form>
    </div>
</x-layouts::auth>