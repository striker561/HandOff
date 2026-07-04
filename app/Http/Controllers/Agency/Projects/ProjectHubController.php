<?php

namespace App\Http\Controllers\Agency\Projects;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureProjectAccess;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Renders agency project hub pages.
 *
 * Project loading and ProjectPolicy::view are enforced by EnsureProjectAccess middleware
 * before these actions run. Livewire components handle interactivity and call services directly.
 */
class ProjectHubController extends Controller
{
    public function overview(Request $request, ProjectService $projects): View
    {
        $project = $this->project($request);

        return view('pages.agency.projects.overview', [
            'project' => $project,
            'section' => 'overview',
            'overview' => $projects->getProjectOverview($project),
        ]);
    }

    public function milestones(Request $request): View
    {
        return $this->page($request, 'milestones', 'pages.agency.projects.milestones');
    }

    public function deliverables(Request $request): View
    {
        $milestoneUniqueId = $request->string('milestone')->toString();

        return view('pages.agency.projects.deliverables', [
            'project' => $this->project($request),
            'section' => 'deliverables',
            'milestoneUniqueId' => $milestoneUniqueId !== '' ? $milestoneUniqueId : null,
        ]);
    }

    public function credentials(Request $request): View
    {
        return $this->page($request, 'credentials', 'pages.agency.projects.credentials');
    }

    public function meetings(Request $request): View
    {
        return $this->page($request, 'meetings', 'pages.agency.projects.meetings');
    }

    protected function page(Request $request, string $section, string $view): View
    {
        return view($view, [
            'project' => $this->project($request),
            'section' => $section,
        ]);
    }

    protected function project(Request $request): Project
    {
        /** @var Project $project */
        $project = $request->attributes->get(EnsureProjectAccess::PROJECT_ATTRIBUTE);

        return $project;
    }
}
