@if ($item['disabled'] ?? false)
    @if (isset($item['badge']))
        <flux:sidebar.item :icon="$item['icon']" href="#" :current="false" :accent="false" :badge="__($item['badge'])"
            aria-disabled="true">
            {{ __($item['label']) }}
        </flux:sidebar.item>
    @else
        <flux:sidebar.item :icon="$item['icon']" href="#" :current="false" :accent="false" aria-disabled="true">
            {{ __($item['label']) }}
        </flux:sidebar.item>
    @endif
@else
    <flux:sidebar.item :icon="$item['icon']" :href="route($item['route'])" :accent="false"
        :current="request()->routeIs($item['active'] ?? $item['route'])" wire:navigate>
        {{ __($item['label']) }}
    </flux:sidebar.item>
@endif