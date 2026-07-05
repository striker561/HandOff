@props(['project', 'overview'])

@php
    use App\Enums\Milestone\MilestoneStatus;

    $projectRoutes = fn(string $name, array $params = []) => route(
        $name,
        array_merge(['projectUniqueId' => $project->unique_id], $params),
    );
@endphp

<div class="project-overview">
    <section class="project-overview__hero handoff-panel">
        <div class="handoff-panel__body">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1 space-y-3">
                    <flux:text class="project-overview__eyebrow">{{ __('Project progress') }}</flux:text>

                    <div class="flex flex-wrap items-end gap-x-4 gap-y-2">
                        <p class="project-overview__progress-value">
                            {{ number_format($overview->progressPercentage, 0) }}%
                        </p>
                        <flux:text class="pb-1">
                            {{ trans_choice(':count milestone complete|:count milestones complete', $overview->milestonesCompleted, ['count' => $overview->milestonesCompleted]) }}
                            @if ($overview->milestonesTotal > 0)
                                <span class="text-brand-600/60 dark:text-brand-300/60">
                                    · {{ $overview->milestonesTotal }} {{ __('total') }}
                                </span>
                            @endif
                        </flux:text>
                    </div>

                    <flux:progress :value="$overview->progressPercentage" class="project-overview__progress-bar" />

                    @if ($overview->milestonesTotal > 0)
                        <div class="flex flex-wrap gap-2 pt-1">
                            @if ($overview->milestonesInProgress > 0)
                                <flux:badge color="blue" size="sm">
                                    {{ $overview->milestonesInProgress }} {{ __('in progress') }}
                                </flux:badge>
                            @endif
                            @if ($overview->milestonesPending > 0)
                                <flux:badge color="gray" size="sm">
                                    {{ $overview->milestonesPending }} {{ __('pending') }}
                                </flux:badge>
                            @endif
                            @if ($overview->milestonesCompleted > 0)
                                <flux:badge color="lime" size="sm">
                                    {{ $overview->milestonesCompleted }} {{ __('completed') }}
                                </flux:badge>
                            @endif
                        </div>
                    @endif
                </div>

                <dl class="project-overview__meta-grid shrink-0">
                    <div class="project-overview__meta-item">
                        <dt class="project-overview__meta-label">{{ __('Budget') }}</dt>
                        <dd class="project-overview__meta-value">
                            @if ($project->formatted_budget)
                                {{ $project->formatted_budget }}
                            @else
                                <span class="text-brand-600/50 dark:text-brand-300/50">{{ __('Not set') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="project-overview__meta-item">
                        <dt class="project-overview__meta-label">{{ __('Start date') }}</dt>
                        <dd class="project-overview__meta-value">
                            {{ $project->start_date?->format('M j, Y') ?? __('No date') }}
                        </dd>
                    </div>
                    <div class="project-overview__meta-item">
                        <dt class="project-overview__meta-label">{{ __('Due date') }}</dt>
                        <dd class="project-overview__meta-value">
                            {{ $project->formatted_due_date ?? __('No date') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <div class="handoff-stat-grid">
        <a href="{{ $projectRoutes('agency.projects.milestones') }}" wire:navigate class="project-overview__stat-link">
            <flux:card class="handoff-stat-card h-full">
                <div class="flex items-center gap-4">
                    <div class="handoff-stat-card__icon">
                        <flux:icon.flag variant="mini" class="size-5" />
                    </div>
                    <div>
                        <p class="handoff-stat-card__value">{{ $overview->milestonesTotal }}</p>
                        <p class="handoff-stat-card__label">{{ __('Milestones') }}</p>
                        @if ($overview->milestonesInProgress > 0)
                            <p class="project-overview__stat-hint">
                                {{ $overview->milestonesInProgress }} {{ __('in progress') }}
                            </p>
                        @endif
                    </div>
                </div>
            </flux:card>
        </a>

        <a href="{{ $projectRoutes('agency.projects.deliverables') }}" wire:navigate
            class="project-overview__stat-link">
            <flux:card class="handoff-stat-card h-full">
                <div class="flex items-center gap-4">
                    <div class="handoff-stat-card__icon">
                        <flux:icon.document-text variant="mini" class="size-5" />
                    </div>
                    <div>
                        <p class="handoff-stat-card__value">{{ $overview->deliverablesTotal }}</p>
                        <p class="handoff-stat-card__label">{{ __('Deliverables') }}</p>
                        @if ($overview->deliverablesApproved > 0)
                            <p class="project-overview__stat-hint">
                                {{ $overview->deliverablesApproved }} {{ __('approved') }}
                            </p>
                        @endif
                    </div>
                </div>
            </flux:card>
        </a>

        <a href="{{ $projectRoutes('agency.projects.credentials') }}" wire:navigate class="project-overview__stat-link">
            <flux:card class="handoff-stat-card h-full">
                <div class="flex items-center gap-4">
                    <div class="handoff-stat-card__icon">
                        <flux:icon.key variant="mini" class="size-5" />
                    </div>
                    <div>
                        <p class="handoff-stat-card__value">{{ $overview->credentialsTotal }}</p>
                        <p class="handoff-stat-card__label">{{ __('Credentials') }}</p>
                    </div>
                </div>
            </flux:card>
        </a>

        <a href="{{ $projectRoutes('agency.projects.meetings') }}" wire:navigate class="project-overview__stat-link">
            <flux:card class="handoff-stat-card h-full">
                <div class="flex items-center gap-4">
                    <div class="handoff-stat-card__icon">
                        <flux:icon.calendar-days variant="mini" class="size-5" />
                    </div>
                    <div>
                        <p class="handoff-stat-card__value">{{ $overview->meetingsUpcoming }}</p>
                        <p class="handoff-stat-card__label">{{ __('Upcoming meetings') }}</p>
                        @if ($overview->meetingsTotal > $overview->meetingsUpcoming)
                            <p class="project-overview__stat-hint">
                                {{ $overview->meetingsTotal }} {{ __('total') }}
                            </p>
                        @endif
                    </div>
                </div>
            </flux:card>
        </a>
    </div>

    <div class="project-overview__grid">
        <section class="project-overview__pipeline handoff-panel">
            <header class="project-overview__section-header">
                <flux:heading size="sm">{{ __('Milestone pipeline') }}</flux:heading>
                <x-ui.button :href="$projectRoutes('agency.projects.milestones')" wire:navigate
                    class="!w-auto px-3 py-1.5 text-xs sm:text-sm">
                    {{ __('View all') }}
                </x-ui.button>
            </header>

            @forelse ($overview->milestones as $milestone)
                <article @class([
                    'project-overview__pipeline-item',
                    'project-overview__pipeline-item--completed' => $milestone->is_completed,
                    'project-overview__pipeline-item--active' =>
                        $milestone->status === MilestoneStatus::IN_PROGRESS,
                ])>
                    <div @class([
                        'project-overview__pipeline-step',
                        'project-overview__pipeline-step--completed' => $milestone->is_completed,
                        'project-overview__pipeline-step--in-progress' =>
                            $milestone->status === MilestoneStatus::IN_PROGRESS,
                        'project-overview__pipeline-step--pending' =>
                            $milestone->status === MilestoneStatus::PENDING,
                    ])>
                        @if ($milestone->is_completed)
                            <flux:icon.check variant="mini" class="size-4" />
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </div>

                    <div class="min-w-0 flex-1 space-y-2">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <flux:heading size="sm" class="truncate">{{ $milestone->name }}</flux:heading>
                            <flux:badge :color="$milestone->status->badgeColor()" size="sm">
                                {{ $milestone->status->label() }}
                            </flux:badge>
                        </div>

                        <div
                            class="text-brand-700/70 dark:text-brand-200/70 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                            @if ($milestone->due_date)
                                <span>{{ __('Due :date', ['date' => $milestone->due_date->format('M j, Y')]) }}</span>
                            @endif
                            <span>{{ trans_choice(':count deliverable|:count deliverables', $milestone->deliverables_count, ['count' => $milestone->deliverables_count]) }}</span>
                        </div>

                        @if ($milestone->progress_percentage > 0)
                            <flux:progress :value="$milestone->progress_percentage" class="project-overview__mini-progress" />
                        @endif
                    </div>
                </article>
            @empty
                <x-ui.empty-state compact icon="flag" :heading="__('No milestones yet')"
                    :text="__('Add the first phase to track progress and attach deliverables.')">
                    <x-slot:actions>
                        <x-ui.button :href="$projectRoutes('agency.projects.milestones')" wire:navigate icon="plus"
                            class="!w-auto">
                            {{ __('Add milestone') }}
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.empty-state>
            @endforelse
        </section>

        <div class="project-overview__sidebar">
            <section class="handoff-panel">
                <header class="project-overview__section-header">
                    <flux:heading size="sm">{{ __('Recent deliverables') }}</flux:heading>
                    <x-ui.button :href="$projectRoutes('agency.projects.deliverables')" wire:navigate
                        class="!w-auto px-3 py-1.5 text-xs sm:text-sm">
                        {{ __('View all') }}
                    </x-ui.button>
                </header>

                @forelse ($overview->recentDeliverables as $deliverable)
                    <article class="project-overview__list-item">
                        <div class="min-w-0 flex-1">
                            <p class="text-brand-900 dark:text-brand-50 truncate font-medium">{{ $deliverable->name }}
                            </p>
                            <p class="text-brand-700/70 dark:text-brand-200/70 mt-0.5 truncate text-sm">
                                {{ $deliverable->milestone?->name ?? __('Unassigned') }}
                            </p>
                        </div>
                        <flux:badge :color="$deliverable->status->badgeColor()" size="sm">
                            {{ $deliverable->status->label() }}
                        </flux:badge>
                    </article>
                @empty
                    <x-ui.empty-state compact icon="document-text" :heading="__('No deliverables yet')"
                        :text="__('Deliverables will appear here as you upload work for each milestone.')" />
                @endforelse
            </section>

            <section class="handoff-panel">
                <header class="project-overview__section-header">
                    <flux:heading size="sm">{{ __('Next meeting') }}</flux:heading>
                    <x-ui.button :href="$projectRoutes('agency.projects.meetings')" wire:navigate
                        class="!w-auto px-3 py-1.5 text-xs sm:text-sm">
                        {{ __('Schedule') }}
                    </x-ui.button>
                </header>

                @if ($overview->nextMeeting)
                    <div class="project-overview__meeting">
                        <flux:heading size="sm">{{ $overview->nextMeeting->title }}</flux:heading>
                        <flux:text class="mt-2">
                            {{ $overview->nextMeeting->scheduled_at->format('l, M j · g:i A') }}
                        </flux:text>
                        @if ($overview->nextMeeting->duration_minutes)
                            <flux:text class="mt-1 text-sm">
                                {{ trans_choice(':count minute|:count minutes', $overview->nextMeeting->duration_minutes, ['count' => $overview->nextMeeting->duration_minutes]) }}
                            </flux:text>
                        @endif
                    </div>
                @else
                    <x-ui.empty-state compact icon="calendar-days" :heading="__('No upcoming meetings')"
                        :text="__('Schedule a kickoff or review when you are ready.')" />
                @endif
            </section>
        </div>
    </div>

    @if ($project->description)
        <section class="handoff-panel">
            <div class="handoff-panel__body">
                <flux:text class="project-overview__eyebrow">{{ __('About this project') }}</flux:text>
                <flux:text class="mt-3 leading-relaxed">{{ $project->description }}</flux:text>
            </div>
        </section>
    @endif
</div>
