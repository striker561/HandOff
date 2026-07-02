@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'viewable' => false,
])

@php
    $inputId = $name ?? $attributes->get('id');
    $hasError = $name && $errors->has($name);
@endphp

<div class="handoff-field" @if ($viewable) x-data="{ show: false }" @endif>
    @if ($label)
        <label @if ($inputId) for="{{ $inputId }}" @endif class="handoff-label">
            {{ $label }}
        </label>
    @endif

    <div class="handoff-input-wrap">
        <input
            @if ($viewable)
                :type="show ? 'text' : 'password'"
            @else
                type="{{ $type }}"
            @endif
            @if ($name) name="{{ $name }}" id="{{ $inputId }}" @endif
            {{ $attributes->class([
                'handoff-input',
                'pr-10' => $viewable,
                'border-red-400 focus:border-red-500 dark:border-red-500' => $hasError,
            ]) }}
        />

        @if ($viewable)
            <button type="button" class="handoff-input-toggle" @click="show = !show"
                :aria-label="show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'">
                <flux:icon.eye x-show="!show" variant="micro" />
                <flux:icon.eye-slash x-show="show" x-cloak variant="micro" />
            </button>
        @endif
    </div>

    @if ($name)
        @error($name)
            <p class="handoff-error">{{ $message }}</p>
        @enderror
    @endif
</div>
