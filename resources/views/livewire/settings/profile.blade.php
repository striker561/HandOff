<?php

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Profile settings')] class extends Component {
    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-5">
            <x-handoff-input wire:model="name" name="name" :label="__('Name')" type="text" required autofocus
                autocomplete="name" />

            <x-handoff-input wire:model="email" name="email" :label="__('Email')" type="email" required
                autocomplete="email" />

            <div class="flex items-center gap-4 pt-2">
                <x-handoff-button type="submit" icon="check" class="!w-auto">
                    {{ __('Save changes') }}
                </x-handoff-button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>