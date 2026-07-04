@props([
    'project',
    'section',
    'contentPanel' => true,
])

@php
    $tabs = [
        'overview' => [
            'label' => __('Overview'),
            'route' => 'agency.projects.show',
        ],
        'milestones' => [
            'label' => __('Milestones'),
            'route' => 'agency.projects.milestones',
        ],
        'deliverables' => [
            'label' => __('Deliverables'),
            'route' => 'agency.projects.deliverables',
        ],
        'credentials' => [
            'label' => __('Credentials'),
            'route' => 'agency.projects.credentials',
        ],
        'meetings' => [
            'label' => __('Meetings'),
            'route' => 'agency.projects.meetings',
        ],
    ];
@endphp

<x-layouts::app :title="$project->name">
    <div class="handoff-page handoff-page--wide handoff-page--hub">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('agency.projects.index') }}" wire:navigate>
                {{ __('Projects') }}
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $project->name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <header class="handoff-page__header">
            <div class="min-w-0 space-y-1">
                <flux:heading size="xl">{{ $project->name }}</flux:heading>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <flux:text class="handoff-page__lede">{{ $project->client_display_name }}</flux:text>
                    <flux:badge :color="$project->status->badgeColor()" size="sm">
                        {{ $project->status->label() }}
                    </flux:badge>
                </div>
            </div>
        </header>

        <nav class="settings-layout__tabs" aria-label="{{ __('Project sections') }}">
            @foreach ($tabs as $tabKey => $tab)
                <a href="{{ route($tab['route'], ['projectUniqueId' => $project->unique_id]) }}" wire:navigate
                    @class([
                        'settings-layout__tab',
                        'settings-layout__tab--current' => $section === $tabKey,
                    ])
                    @if ($section === $tabKey) aria-current="page" @endif>
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </nav>

        @if ($contentPanel)
            <div class="settings-layout__card handoff-clip-wrap w-full max-w-none">
                <div class="handoff-clip-frame handoff-clip-frame--ticks">
                    <div class="handoff-clip-shell">
                        <section class="settings-layout__panel handoff-clip-form handoff-clip-form--compact">
                            <div class="settings-layout__content">
                                {{ $slot }}
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>

    @isset($modals)
        {{ $modals }}
    @endisset
</x-layouts::app>
