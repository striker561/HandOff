@php
    $groups = config("navigation.{$variant}.groups", []);
@endphp

<flux:sidebar.header class="handoff-sidebar__header">
    <x-sidebar.logo />
    <flux:sidebar.collapse class="handoff-sidebar__collapse lg:hidden" />
</flux:sidebar.header>

<flux:sidebar.nav>
    @foreach ($groups as $index => $group)
        @if ($index > 0)
            <flux:separator class="handoff-sidebar__separator" />
        @endif

        @if ($group['expandable'] ?? false)
            <flux:sidebar.group expandable icon="{{ $group['icon'] }}" :heading="__($group['heading'])" class="grid">
                @foreach ($group['items'] as $item)
                    @include('layouts.app.sidebars._item', ['item' => $item])
                @endforeach
            </flux:sidebar.group>
        @else
            <flux:sidebar.group :heading="__($group['heading'])" class="grid">
                @foreach ($group['items'] as $item)
                    @include('layouts.app.sidebars._item', ['item' => $item])
                @endforeach
            </flux:sidebar.group>
        @endif
    @endforeach
</flux:sidebar.nav>

<flux:spacer />

@include('layouts.app.sidebars._footer')