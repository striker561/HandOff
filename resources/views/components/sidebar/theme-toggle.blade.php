<div {{ $attributes->class('handoff-theme') }} x-data role="group" aria-label="{{ __('Appearance') }}">
    <div class="handoff-theme__track">
        <button type="button" class="handoff-theme__btn" :class="{ 'is-active': $flux.appearance === 'light' }"
            :aria-pressed="$flux.appearance === 'light'" @click="$flux.appearance = 'light'">
            <flux:icon.sun variant="mini" />
            <span class="sr-only">{{ __('Light') }}</span>
        </button>

        <button type="button" class="handoff-theme__btn" :class="{ 'is-active': $flux.appearance === 'dark' }"
            :aria-pressed="$flux.appearance === 'dark'" @click="$flux.appearance = 'dark'">
            <flux:icon.moon variant="mini" />
            <span class="sr-only">{{ __('Dark') }}</span>
        </button>

        <button type="button" class="handoff-theme__btn" :class="{ 'is-active': $flux.appearance === 'system' }"
            :aria-pressed="$flux.appearance === 'system'" @click="$flux.appearance = 'system'">
            <flux:icon.computer-desktop variant="mini" />
            <span class="sr-only">{{ __('System') }}</span>
        </button>
    </div>
</div>