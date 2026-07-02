<?php

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
    #[Layout('layouts.workspace')]
    #[Title('Profile settings')]
    class extends Component {
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

<div>
    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="w-full space-y-5">
            <x-ui.input wire:model="name" name="name" :label="__('Name')" type="text" required autofocus
                autocomplete="name" />

            <x-ui.input wire:model="email" name="email" :label="__('Email')" type="email" required
                autocomplete="email" />

            <div class="flex items-center justify-center gap-4 pt-2">
                <x-ui.button type="submit" icon="check" class="!w-auto">
                    {{ __('Save changes') }}
                </x-ui.button>
            </div>
        </form>
    </x-settings.layout>
</div>