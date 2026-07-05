<?php

namespace App\Livewire\Agency\Projects;

use App\Concerns\WithNotifications;
use App\Services\ProjectService;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewProject extends Component
{
    use WithNotifications;

    #[Locked]
    public ?string $uniqueId = null;

    #[Locked]
    public string $name = '';

    #[Locked]
    public ?string $description = null;

    #[Locked]
    public string $clientName = '';

    #[Locked]
    public string $statusLabel = '';

    #[Locked]
    public string $statusBadgeColor = 'gray';

    #[Locked]
    public ?string $formattedBudget = null;

    #[Locked]
    public ?string $formattedStartDate = null;

    #[Locked]
    public ?string $formattedDueDate = null;

    #[Locked]
    public int $progressPercentage = 0;

    private ProjectService $projectService;

    public function boot(ProjectService $projectService): void
    {
        $this->projectService = $projectService;
    }

    #[On('open-project-view')]
    public function open(string $uniqueId): void
    {
        $project = $this->projectService->findProject($uniqueId);

        if ($project === null) {
            $this->notifyError(__('Project not found.'));

            return;
        }

        $this->authorize('view', $project);

        $this->uniqueId = $project->unique_id;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->clientName = $project->client_display_name;
        $this->statusLabel = $project->status->label();
        $this->statusBadgeColor = $project->status->badgeColor();
        $this->formattedBudget = $project->formatted_budget;
        $this->formattedStartDate = $project->start_date?->format('M j, Y');
        $this->formattedDueDate = $project->formatted_due_date;
        $this->progressPercentage = (int) round($this->projectService->calculateProgress($project));

        $this->modal('view-project')->show();
    }

    public function close(): void
    {
        $this->uniqueId = null;
        $this->name = '';
        $this->description = null;
        $this->clientName = '';
        $this->statusLabel = '';
        $this->statusBadgeColor = 'gray';
        $this->formattedBudget = null;
        $this->formattedStartDate = null;
        $this->formattedDueDate = null;
        $this->progressPercentage = 0;

        $this->modal('view-project')->close();
    }

    public function render()
    {
        return view('livewire.agency.projects.view-project');
    }
}
