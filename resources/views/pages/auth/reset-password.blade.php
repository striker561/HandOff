<x-layouts::auth :title="__('Reset password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

        <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <x-handoff-input name="email" :label="__('Email')" type="email" required autofocus autocomplete="email"
                :value="old('email', $request->email)" />

            <x-handoff-input name="password" :label="__('Password')" viewable required autocomplete="new-password" />

            <x-handoff-input name="password_confirmation" :label="__('Confirm password')" viewable required
                autocomplete="new-password" />

            <x-handoff-button type="submit" icon="lock-closed">
                {{ __('Reset password') }}
            </x-handoff-button>
        </form>
    </div>
</x-layouts::auth>