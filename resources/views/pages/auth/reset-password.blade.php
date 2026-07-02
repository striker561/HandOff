<x-layouts::auth :title="__('Reset password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

        <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <x-ui.input name="email" :label="__('Email')" type="email" required autofocus autocomplete="email"
                :value="old('email', $request->email)" />

            <x-ui.input name="password" :label="__('Password')" viewable required autocomplete="new-password" />

            <x-ui.input name="password_confirmation" :label="__('Confirm password')" viewable required
                autocomplete="new-password" />

            <x-ui.button type="submit" icon="lock-closed">
                {{ __('Reset password') }}
            </x-ui.button>
        </form>
    </div>
</x-layouts::auth>