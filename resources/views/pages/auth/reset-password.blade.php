<x-layouts::auth :title="__('Reset password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

        <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-6">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <flux:input name="email" :label="__('Email')" type="email" required autofocus autocomplete="email"
                :value="old('email', $request->email)" />

            <flux:input name="password" :label="__('Password')" type="password" required autocomplete="new-password"
                viewable />

            <flux:input name="password_confirmation" :label="__('Confirm password')" type="password" required
                autocomplete="new-password" />

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Reset password') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>