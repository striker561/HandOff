@props([
    'name',
    'heading',
    'description' => null,
    'confirmLabel',
    'cancelLabel' => null,
    'confirmVariant' => 'danger',
    'confirmAction',
    'cancelAction' => null,
    'class' => 'min-w-[22rem]',
])

<flux:modal :name="$name" :class="$class">
    <div class="space-y-6">
        <div class="space-y-2">
            <flux:heading size="lg">{{ $heading }}</flux:heading>
            @if ($description)
                <flux:text>{{ $description }}</flux:text>
            @endif
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                @if ($cancelAction)
                    <flux:button variant="ghost" wire:click="{{ $cancelAction }}">
                        {{ $cancelLabel ?? __('Cancel') }}
                    </flux:button>
                @else
                    <flux:button variant="ghost">{{ $cancelLabel ?? __('Cancel') }}</flux:button>
                @endif
            </flux:modal.close>
            <flux:button :variant="$confirmVariant" wire:click="{{ $confirmAction }}">
                {{ $confirmLabel }}
            </flux:button>
        </div>
    </div>
</flux:modal>
