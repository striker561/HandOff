<?php

namespace App\Services;

use App\Data\Projects\CreateProjectData;
use App\Data\Projects\ProjectOverviewData;
use App\Data\Projects\ProjectOverviewStats;
use App\Enums\Milestone\MilestoneStatus;
use App\Enums\Project\ProjectAction;
use App\Enums\Project\ProjectStatus;
use App\Events\Project\ProjectEvent;
use App\Models\Credential;
use App\Models\Deliverable;
use App\Models\Meeting;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ProjectService extends BaseCRUDService
{
    private const PROJECT_OVERVIEW_CACHE_MINUTES = 5;

    public function __construct(private ClientService $clients) {}

    protected function getModel(): string
    {
        return Project::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'description'];
    }

    protected function filterableColumns(): array
    {
        return ['status', 'client_unique_id'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'status', 'budget', 'due_date', 'created_at', 'updated_at'];
    }

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Project::query()->with('client');
        $query = $this->applyFilters($query, $filters);

        return $this->paginateQuery($query, $filters);
    }

    public function findProject(string $uniqueId): ?Project
    {
        return Project::query()
            ->with('client')
            ->where('unique_id', $uniqueId)
            ->first();
    }

    public function createProject(CreateProjectData $data, User $performedBy): Project
    {
        if ($this->clients->findClient($data->clientUniqueId) === null) {
            $this->fieldError('client_unique_id', __('Selected client was not found.'));
        }

        /** @var Project $project */
        $project = $this->create($data->toAttributes());

        ProjectEvent::dispatch(
            $project,
            ProjectAction::CREATED,
            $performedBy,
            []
        );

        return $project;
    }

    public function calculateProgress(Project $project): float
    {
        return $this->calculateProgressFromMilestones($project->milestones);
    }

    /**
     * @param  Collection<int, Milestone>  $milestones
     */
    public function calculateProgressFromMilestones(Collection $milestones): float
    {
        if ($milestones->isEmpty()) {
            return 0.0;
        }

        $completedCount = $milestones->where('is_completed', true)->count();

        return round(($completedCount / $milestones->count()) * 100, 2);
    }

    public function getProjectOverview(Project $project): ProjectOverviewData
    {
        $stats = Cache::remember(
            $this->projectOverviewStatsCacheKey($project->unique_id),
            now()->addMinutes(self::PROJECT_OVERVIEW_CACHE_MINUTES),
            fn (): array => $this->buildProjectOverviewStats($project)->toArray(),
        );

        return $this->assembleProjectOverview(
            $project,
            ProjectOverviewStats::fromArray($stats),
        );
    }

    public function forgetProjectOverview(Project|string $project): void
    {
        $uniqueId = $project instanceof Project ? $project->unique_id : $project;

        Cache::forget($this->projectOverviewStatsCacheKey($uniqueId));
    }

    private function buildProjectOverviewStats(Project $project): ProjectOverviewStats
    {
        $deliverableStats = Deliverable::statusCountsForProject($project->unique_id);
        $meetingStats = Meeting::overviewStatsForProject($project->unique_id);

        return new ProjectOverviewStats(
            deliverablesTotal: $deliverableStats->total,
            deliverablesApproved: $deliverableStats->approved,
            credentialsTotal: Credential::countForProject($project->unique_id),
            meetingsTotal: $meetingStats->total,
            meetingsUpcoming: $meetingStats->upcoming,
        );
    }

    private function assembleProjectOverview(Project $project, ProjectOverviewStats $stats): ProjectOverviewData
    {
        $milestones = Milestone::pipelineForProject($project->unique_id);

        $milestonesTotal = $milestones->count();
        $milestonesCompleted = $milestones->where('status', MilestoneStatus::COMPLETED)->count();
        $milestonesInProgress = $milestones->where('status', MilestoneStatus::IN_PROGRESS)->count();
        $milestonesPending = $milestones->where('status', MilestoneStatus::PENDING)->count();

        $progressPercentage = $this->calculateProgressFromMilestones($milestones);

        return new ProjectOverviewData(
            progressPercentage: $progressPercentage,
            milestonesTotal: $milestonesTotal,
            milestonesCompleted: $milestonesCompleted,
            milestonesInProgress: $milestonesInProgress,
            milestonesPending: $milestonesPending,
            deliverablesTotal: $stats->deliverablesTotal,
            deliverablesApproved: $stats->deliverablesApproved,
            credentialsTotal: $stats->credentialsTotal,
            meetingsTotal: $stats->meetingsTotal,
            meetingsUpcoming: $stats->meetingsUpcoming,
            milestones: $milestones,
            recentDeliverables: Deliverable::recentForProject($project->unique_id),
            nextMeeting: Meeting::nextUpcomingForProject($project->unique_id),
        );
    }

    private function projectOverviewStatsCacheKey(string $projectUniqueId): string
    {
        return "project-overview-stats:{$projectUniqueId}";
    }

    public function changeStatus(Project $project, ProjectStatus $status, User $performedBy): Project
    {
        $fromStatus = $project->status;
        $project->update(
            ['status' => $status]
        );

        ProjectEvent::dispatch(
            $project,
            ProjectAction::STATUS_CHANGED,
            $performedBy,
            [
                'from_status' => $fromStatus instanceof ProjectStatus ? $fromStatus->value : $fromStatus,
                'to_status' => $status->value,
            ]
        );

        return $project->fresh();
    }

    /**
     * @throws ValidationException
     */
    private function fieldError(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}
