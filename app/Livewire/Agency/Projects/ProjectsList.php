<?php

namespace App\Livewire\Agency\Projects;

use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Projects')]
class ProjectsList extends Component
{
    use WithPagination;

    #[Url(as: 'search', history: true)]
    public string $search = '';

    #[Url(as: 'sort', history: true)]
    public string $sort = 'created_at';

    #[Url(as: 'direction', history: true)]
    public string $direction = 'desc';

    #[Url(as: 'status', history: true)]
    public string $filterStatus = '';

    private ProjectService $projectService;

    public function boot(ProjectService $projectService): void
    {
        $this->projectService = $projectService;
    }

    public function mount(): void
    {
        $this->authorize('create', Project::class);
    }

    #[On('project-created')]
    #[On('project-updated')]
    public function refreshProjects(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->direction = 'asc';
        }
    }

    public function openSaveProject(): void
    {
        $this->dispatch('open-save-project')->to(SaveProject::class);
    }

    public function editProject(string $uniqueId): void
    {
        $this->dispatch('open-save-project', uniqueId: $uniqueId)->to(SaveProject::class);
    }

    public function viewProject(string $uniqueId): void
    {
        $this->dispatch('open-project-view', uniqueId: $uniqueId)->to(ViewProject::class);
    }

    #[Computed]
    public function projects(): LengthAwarePaginator
    {
        $filters = [
            'search' => $this->search,
            'sort' => $this->sort,
            'direction' => $this->direction,
        ];

        if ($this->filterStatus) {
            $filters['status'] = $this->filterStatus;
        }

        return $this->projectService->getAll($filters);
    }

    public function render()
    {
        return view('livewire.agency.projects.projects-list');
    }
}
