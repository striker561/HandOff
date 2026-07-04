<?php

namespace App\Services;

use App\Data\Projects\CreateProjectData;
use App\Enums\Project\ProjectAction;
use App\Enums\Project\ProjectStatus;
use App\Events\Project\ProjectEvent;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ProjectService extends BaseCRUDService
{
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
        $milestones = $project->milestones;

        if ($milestones->isEmpty()) {
            return 0;
        }

        $completedCount = $milestones->where(
            'is_completed',
            true
        )->count();

        return round(($completedCount / $milestones->count()) * 100, 2);
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
