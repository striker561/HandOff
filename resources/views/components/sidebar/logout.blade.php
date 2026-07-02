@props([
    'iconOnly' => false,
])

<form method="POST" action="{{ route('logout') }}" {{ $attributes }}>
    @csrf
    <button type="submit" @class([
        'handoff-sidebar-action',
        'handoff-sidebar-action--icon' => $iconOnly,
    ])>
        <flux:icon.arrow-right-start-on-rectangle variant="mini" class="size-4" />
        @unless ($iconOnly)
            <span>{{ __('Log out') }}</span>
        @endunless
        @if ($iconOnly)
            <span class="sr-only">{{ __('Log out') }}</span>
        @endif
    </button>
</form>
