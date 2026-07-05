@props([
    'title' => null,
    'mobileTitle' => null,
    'meta' => null,
    'href' => null,
    'wireClick' => null,
])

@php
    $displayMobileTitle = $mobileTitle ?? $title;
    $isTappable = filled($href) || filled($wireClick);
@endphp

<flux:table.cell variant="strong" {{ $attributes->class(['min-w-0']) }}>
    <div class="min-w-0 whitespace-normal">
        @if ($href)
            <a href="{{ $href }}" wire:navigate class="handoff-data-table__row-target">
            @elseif ($wireClick)
                <button type="button" wire:click="{{ $wireClick }}" class="handoff-data-table__row-target">
        @endif

        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="min-w-0 font-semibold sm:truncate">
                    <span class="sm:hidden">{{ $displayMobileTitle }}</span>
                    <span class="hidden sm:inline">{{ $title }}</span>
                </div>

                @if ($meta)
                    <div class="handoff-data-table__row-meta">{{ $meta }}</div>
                @endif

                @isset($mobile)
                    <div class="handoff-data-table__row-mobile">{{ $mobile }}</div>
                @endisset
            </div>

            @if ($isTappable)
                <x-dynamic-component :component="'flux::icon.chevron-right'" variant="mini" class="handoff-data-table__row-chevron" />
            @endif
        </div>

        @if ($href)
            </a>
        @elseif ($wireClick)
            </button>
        @endif

        @isset($action)
            <div class="handoff-data-table__mobile-actions">{{ $action }}</div>
        @endisset
    </div>
</flux:table.cell>
