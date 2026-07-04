<?php

use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\User\AccountRole;
use App\Livewire\Agency\Projects\Credentials\CreateCredential;
use App\Livewire\Agency\Projects\Credentials\ViewCredential;
use App\Livewire\Agency\Projects\Deliverables\CreateDeliverable;
use App\Livewire\Agency\Projects\Deliverables\DeliverablesList;
use App\Livewire\Agency\Projects\Meetings\ScheduleMeeting;
use App\Livewire\Agency\Projects\Milestones\CreateMilestone;
use App\Livewire\Agency\Projects\Milestones\MilestonesList;
use App\Livewire\Agency\Projects\ViewProject;
use App\Models\Credential;
use App\Models\Deliverable;
use App\Models\Meeting;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('loads the project overview page for admins', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create([
        'client_unique_id' => $client->unique_id,
        'name' => 'Hub Project',
    ]);

    $this->actingAs($admin)
        ->get(route('agency.projects.show', ['projectUniqueId' => $project->unique_id]))
        ->assertSuccessful()
        ->assertSee('Hub Project')
        ->assertSee(__('Overview'));
});

it('loads the project milestones page for admins', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    $this->actingAs($admin)
        ->get(route('agency.projects.milestones', ['projectUniqueId' => $project->unique_id]))
        ->assertSuccessful()
        ->assertSeeLivewire(MilestonesList::class);
});

it('forbids client users from project hub pages', function () {
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    $this->actingAs($client)
        ->get(route('agency.projects.show', ['projectUniqueId' => $project->unique_id]))
        ->assertForbidden();
});

it('returns not found for unknown project unique ids', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('agency.projects.show', ['projectUniqueId' => Str::uuid()->toString()]))
        ->assertNotFound();
});

it('shows open project link in the view flyout', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create([
        'client_unique_id' => $client->unique_id,
        'name' => 'Flyout Project',
    ]);

    Livewire::actingAs($admin)
        ->test(ViewProject::class)
        ->call('open', uniqueId: $project->unique_id)
        ->assertSee(__('Open project'))
        ->assertSee(route('agency.projects.show', ['projectUniqueId' => $project->unique_id]));
});

it('creates a milestone from the modal', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    Livewire::actingAs($admin)
        ->test(CreateMilestone::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('name', 'Discovery Phase')
        ->set('description', 'Initial research')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('milestone-created');

    expect(Milestone::query()->where('name', 'Discovery Phase')->exists())->toBeTrue();
});

it('lists milestones on the project hub', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Design Phase',
        'order' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(MilestonesList::class, ['projectUniqueId' => $project->unique_id])
        ->assertSee('Design Phase');
});

it('links milestones to filtered deliverables page', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'order' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(MilestonesList::class, ['projectUniqueId' => $project->unique_id])
        ->assertSee(route('agency.projects.deliverables', [
            'projectUniqueId' => $project->unique_id,
            'milestone' => $milestone->unique_id,
        ], false));
});

it('filters deliverables by milestone query string on the deliverables page', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestoneA = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $milestoneB = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 2]);

    Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestoneA->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'name' => 'Alpha Deliverable',
    ]);
    Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestoneB->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'name' => 'Beta Deliverable',
    ]);

    $this->actingAs($admin)
        ->get(route('agency.projects.deliverables', [
            'projectUniqueId' => $project->unique_id,
            'milestone' => $milestoneA->unique_id,
        ]))
        ->assertSuccessful()
        ->assertSee('Alpha Deliverable')
        ->assertDontSee('Beta Deliverable');
});

it('creates a deliverable linked to a milestone', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'order' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(CreateDeliverable::class)
        ->call('open', projectUniqueId: $project->unique_id, milestoneUniqueId: $milestone->unique_id)
        ->set('name', 'Wireframes')
        ->set('type', 'design')
        ->set('milestone_unique_id', $milestone->unique_id)
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('deliverable-created');

    expect(Deliverable::query()->where('name', 'Wireframes')->value('milestone_unique_id'))
        ->toBe($milestone->unique_id);
});

it('filters deliverables by milestone in livewire list', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestoneA = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $milestoneB = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 2]);

    Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestoneA->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'name' => 'Alpha Deliverable',
    ]);
    Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestoneB->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'name' => 'Beta Deliverable',
    ]);

    Livewire::actingAs($admin)
        ->test(DeliverablesList::class, [
            'projectUniqueId' => $project->unique_id,
            'milestoneUniqueId' => $milestoneA->unique_id,
        ])
        ->assertSee('Alpha Deliverable')
        ->assertDontSee('Beta Deliverable');
});

it('approves a deliverable from the list', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'status' => DeliverableStatus::DRAFT,
    ]);

    Livewire::actingAs($admin)
        ->test(DeliverablesList::class, ['projectUniqueId' => $project->unique_id])
        ->call('approve', $deliverable->unique_id)
        ->assertDispatched('deliverable-created');

    expect($deliverable->fresh()->status)->toBe(DeliverableStatus::APPROVED);
});

it('creates an encrypted credential', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    Livewire::actingAs($admin)
        ->test(CreateCredential::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('name', 'Staging Login')
        ->set('type', 'login')
        ->set('username', 'admin')
        ->set('password', 'secret-password')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('credential-created');

    $credential = Credential::query()->where('name', 'Staging Login')->first();

    expect($credential)->not->toBeNull();
    expect($credential->password)->not->toBe('secret-password');
    expect(Crypt::decryptString($credential->password))->toBe('secret-password');
});

it('reveals a credential password on demand', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $credential = Credential::factory()->create([
        'project_unique_id' => $project->unique_id,
        'password' => Crypt::encryptString('vault-secret'),
    ]);

    Livewire::actingAs($admin)
        ->test(ViewCredential::class)
        ->call('open', uniqueId: $credential->unique_id, projectUniqueId: $project->unique_id)
        ->call('revealPassword')
        ->assertSet('passwordRevealed', true)
        ->assertSet('revealedPassword', 'vault-secret');
});

it('schedules a meeting for a project', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    Livewire::actingAs($admin)
        ->test(ScheduleMeeting::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('title', 'Kickoff Call')
        ->set('scheduled_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('duration_minutes', 60)
        ->set('location', 'meet')
        ->call('schedule')
        ->assertHasNoErrors()
        ->assertDispatched('meeting-scheduled');

    expect(Meeting::query()->where('title', 'Kickoff Call')->exists())->toBeTrue();
});

it('schedules a meeting linked to a deliverable', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'name' => 'Review Deck',
    ]);

    Livewire::actingAs($admin)
        ->test(ScheduleMeeting::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('title', 'Review Session')
        ->set('scheduled_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->set('deliverable_unique_id', $deliverable->unique_id)
        ->call('schedule')
        ->assertHasNoErrors();

    expect(Meeting::query()->where('title', 'Review Session')->value('deliverable_unique_id'))
        ->toBe($deliverable->unique_id);
});
