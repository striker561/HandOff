@props([
    'title' => null,
    'meta' => null,
])

<flux:table.cell variant="strong" {{ $attributes->class(['min-w-0']) }}>
    <div class="min-w-0 whitespace-normal">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1 truncate">{{ $title }}</div>

            @isset($action)
                <div class="shrink-0 sm:hidden">{{ $action }}</div>
            @endisset
        </div>

        @if ($meta)
            <div class="mt-0.5 line-clamp-2 text-sm font-normal text-zinc-500 md:hidden">{{ $meta }}</div>
        @endif

        @isset($mobile)
            <div class="mt-1.5 sm:hidden">{{ $mobile }}</div>
        @endisset
    </div>
</flux:table.cell>
