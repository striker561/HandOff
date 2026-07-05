<?php

use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Deliverable\DeliverableType;
use App\Enums\Meeting\MeetingStatus;
use App\Enums\Milestone\MilestoneStatus;
use App\Enums\User\AccountRole;
use App\Livewire\Agency\Projects\Credentials\SaveCredential;
use App\Livewire\Agency\Projects\Credentials\ViewCredential;
use App\Livewire\Agency\Projects\Deliverables\DeliverablesList;
use App\Livewire\Agency\Projects\Deliverables\SaveDeliverable;
use App\Livewire\Agency\Projects\Meetings\SaveMeeting;
use App\Livewire\Agency\Projects\Milestones\MilestonesList;
use App\Livewire\Agency\Projects\Milestones\SaveMilestone;
use App\Livewire\Agency\Projects\ViewProject;
use App\Livewire\Ui\FileUploader;
use App\Models\Credential;
use App\Models\Deliverable;
use App\Models\DeliverableFile;
use App\Models\Meeting;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('loads the project overview page for admins', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create([
        'client_unique_id' => $client->unique_id,
        'name' => 'Hub Project',
    ]);
    Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Discovery',
        'status' => MilestoneStatus::IN_PROGRESS,
        'order' => 1,
    ]);

    $this->actingAs($admin)
        ->get(route('agency.projects.show', ['projectUniqueId' => $project->unique_id]))
        ->assertSuccessful()
        ->assertSee('Hub Project')
        ->assertSee(__('Overview'))
        ->assertSee(__('Project progress'))
        ->assertSee(__('Milestone pipeline'))
        ->assertSee('Discovery');
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

it('shows guided empty state on milestones tab when none exist', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    $this->actingAs($admin)
        ->get(route('agency.projects.milestones', ['projectUniqueId' => $project->unique_id]))
        ->assertSuccessful()
        ->assertSee(__('No milestones yet'))
        ->assertSee(__('Phases of the handoff'))
        ->assertSee(__('Add milestone'));
});

it('shows milestones-first empty state on deliverables tab when no milestones exist', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    $this->actingAs($admin)
        ->get(route('agency.projects.deliverables', ['projectUniqueId' => $project->unique_id]))
        ->assertSuccessful()
        ->assertSee(__('Add milestones first'))
        ->assertSee(__('Go to milestones'));
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
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('name', 'Discovery Phase')
        ->set('description', 'Initial research')
        ->call('save')
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
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $project->unique_id, milestoneUniqueId: $milestone->unique_id)
        ->set('name', 'Wireframes')
        ->set('type', 'design')
        ->set('milestone_unique_id', $milestone->unique_id)
        ->call('save')
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

it('renders mobile row actions on deliverables list', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'name' => 'Mobile Actions Deliverable',
        'status' => DeliverableStatus::DRAFT,
    ]);

    $html = Livewire::actingAs($admin)
        ->test(DeliverablesList::class, ['projectUniqueId' => $project->unique_id])
        ->html();

    expect($html)
        ->toContain('wire:click="editDeliverable')
        ->toContain('wire:click="submitForReview')
        ->not->toContain('wire:click="approve')
        ->not->toContain('wire:click="reject');
});

it('submits a deliverable for review from the list', function () {
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
        ->call('submitForReview', uniqueId: $deliverable->unique_id);

    expect($deliverable->fresh()->status)->toBe(DeliverableStatus::IN_REVIEW);
});

it('creates an encrypted credential', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    Livewire::actingAs($admin)
        ->test(SaveCredential::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('name', 'Staging Login')
        ->set('type', 'login')
        ->set('username', 'admin')
        ->set('password', 'secret-password')
        ->call('save')
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
        ->test(SaveMeeting::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('title', 'Kickoff Call')
        ->set('scheduled_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('duration_minutes', 60)
        ->set('location', 'meet')
        ->call('save')
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
        ->test(SaveMeeting::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('title', 'Review Session')
        ->set('scheduled_at', now()->addDays(2)->format('Y-m-d\TH:i'))
        ->set('deliverable_unique_id', $deliverable->unique_id)
        ->call('save')
        ->assertHasNoErrors();

    expect(Meeting::query()->where('title', 'Review Session')->value('deliverable_unique_id'))
        ->toBe($deliverable->unique_id);
});

it('updates a milestone from the save modal', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Old Name',
        'order' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $milestone->unique_id)
        ->set('name', 'Updated Name')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('milestone-created');

    expect($milestone->fresh()->name)->toBe('Updated Name');
});

it('shows a read-only status when editing a completed milestone', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->completed()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Done Phase',
        'order' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $milestone->unique_id)
        ->assertSee(__('Completed'))
        ->assertSee(__('Completed automatically when all deliverables in this milestone are approved.'))
        ->assertDontSee('wire:model="status"', false);
});

it('creates a milestone with the selected status', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);

    Livewire::actingAs($admin)
        ->test(SaveMilestone::class)
        ->call('open', projectUniqueId: $project->unique_id)
        ->set('name', 'Build Phase')
        ->set('status', MilestoneStatus::IN_PROGRESS->value)
        ->call('save')
        ->assertHasNoErrors();

    expect(Milestone::query()->where('name', 'Build Phase')->first()->status)
        ->toBe(MilestoneStatus::IN_PROGRESS);
});

it('updates a deliverable from the save modal', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'name' => 'Old Deliverable',
        'type' => DeliverableType::FILE,
        'status' => DeliverableStatus::DRAFT,
    ]);

    Livewire::actingAs($admin)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $deliverable->unique_id)
        ->set('name', 'Updated Deliverable')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('deliverable-created');

    expect($deliverable->fresh()->name)->toBe('Updated Deliverable');
});

it('uploads files when creating a file-based deliverable', function () {
    Storage::fake('deliverables');

    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $file = UploadedFile::fake()->create('wireframes.pdf', 512, 'application/pdf');

    Livewire::actingAs($admin)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $project->unique_id, milestoneUniqueId: $milestone->unique_id)
        ->set('name', 'Wireframes PDF')
        ->set('type', DeliverableType::DESIGN->value)
        ->set('milestone_unique_id', $milestone->unique_id)
        ->set('fileUploaderState.pending', [$file])
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('deliverable-created');

    $deliverable = Deliverable::query()->where('name', 'Wireframes PDF')->first();

    expect($deliverable)->not->toBeNull();

    expect(DeliverableFile::query()->where('deliverable_unique_id', $deliverable->unique_id)->count())->toBe(1);

    $latestFile = DeliverableFile::query()
        ->where('deliverable_unique_id', $deliverable->unique_id)
        ->where('is_latest', true)
        ->first();

    expect($latestFile)->not->toBeNull()
        ->and($latestFile->original_filename)->toBe('wireframes.pdf')
        ->and((int) $latestFile->version)->toBe(1);

    Storage::disk('deliverables')->assertExists($latestFile->file_path);
});

it('uploads multiple files for one deliverable', function () {
    Storage::fake('deliverables');

    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);

    Livewire::actingAs($admin)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $project->unique_id, milestoneUniqueId: $milestone->unique_id)
        ->set('name', 'Asset Pack')
        ->set('type', DeliverableType::FILE->value)
        ->set('milestone_unique_id', $milestone->unique_id)
        ->set('fileUploaderState.pending', [
            UploadedFile::fake()->create('spec.pdf', 256, 'application/pdf'),
            UploadedFile::fake()->image('preview.png'),
        ])
        ->call('save')
        ->assertHasNoErrors();

    $deliverable = Deliverable::query()->where('name', 'Asset Pack')->first();

    expect(DeliverableFile::query()->where('deliverable_unique_id', $deliverable->unique_id)->count())->toBe(2);
});

it('adds another file when editing a draft deliverable', function () {
    Storage::fake('deliverables');

    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'name' => 'Versioned Deliverable',
        'type' => DeliverableType::FILE,
        'status' => DeliverableStatus::DRAFT,
        'version' => 1,
    ]);

    DeliverableFile::factory()->create([
        'deliverable_unique_id' => $deliverable->unique_id,
        'uploaded_by_unique_id' => $admin->unique_id,
        'original_filename' => 'v1.pdf',
        'version' => 1,
        'is_latest' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $deliverable->unique_id)
        ->assertSet('fileUploaderState.existing', fn (array $files): bool => count($files) === 1)
        ->set('fileUploaderState.pending', [UploadedFile::fake()->create('v2.pdf', 256, 'application/pdf')])
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('deliverable-created');

    expect(DeliverableFile::query()->where('deliverable_unique_id', $deliverable->unique_id)->count())->toBe(2);
});

it('allows admins to delete deliverable files on draft deliverables', function () {
    Storage::fake('deliverables');

    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'type' => DeliverableType::FILE,
        'status' => DeliverableStatus::DRAFT,
    ]);

    $existing = DeliverableFile::factory()->create([
        'deliverable_unique_id' => $deliverable->unique_id,
        'uploaded_by_unique_id' => $admin->unique_id,
        'original_filename' => 'v1.pdf',
        'version' => 1,
        'is_latest' => true,
    ]);

    expect(Gate::forUser($admin)->allows('delete', $existing))->toBeTrue();

    Livewire::actingAs($admin)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $deliverable->unique_id)
        ->set('fileUploaderState', [
            'existing' => [],
            'pending' => [],
            'removed_ids' => [$existing->unique_id],
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('deliverable-created');

    expect(DeliverableFile::query()->where('unique_id', $existing->unique_id)->exists())->toBeFalse();
});

it('clears a pending file upload from the uploader', function () {
    $file = UploadedFile::fake()->create('draft.pdf', 128, 'application/pdf');

    Livewire::test(FileUploader::class)
        ->set('state.pending', [$file])
        ->call('removePending', index: 0)
        ->assertSet('state.pending', []);
});

it('allows opening but forbids saving an in-review deliverable', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'name' => 'Locked Deliverable',
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    Livewire::actingAs($admin)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $deliverable->unique_id)
        ->assertSet('name', 'Locked Deliverable')
        ->set('name', 'Should Not Save')
        ->call('save')
        ->assertForbidden();

    expect($deliverable->fresh()->name)->toBe('Locked Deliverable');
});

it('forbids submitting an in-review deliverable for review again', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    Livewire::actingAs($admin)
        ->test(DeliverablesList::class, ['projectUniqueId' => $project->unique_id])
        ->call('submitForReview', uniqueId: $deliverable->unique_id)
        ->assertForbidden();

    expect($deliverable->fresh()->status)->toBe(DeliverableStatus::IN_REVIEW);
});

it('updates a credential from the save modal without changing password', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $credential = Credential::factory()->create([
        'project_unique_id' => $project->unique_id,
        'name' => 'Old Credential',
        'password' => Crypt::encryptString('unchanged-secret'),
    ]);

    Livewire::actingAs($admin)
        ->test(SaveCredential::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $credential->unique_id)
        ->set('name', 'Updated Credential')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('credential-updated');

    expect($credential->fresh()->name)->toBe('Updated Credential');
    expect(Crypt::decryptString($credential->fresh()->password))->toBe('unchanged-secret');
});

it('updates a scheduled meeting from the save modal', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $meeting = Meeting::factory()->create([
        'project_unique_id' => $project->unique_id,
        'title' => 'Old Title',
        'status' => MeetingStatus::SCHEDULED,
        'scheduled_at' => now()->addDay(),
    ]);

    Livewire::actingAs($admin)
        ->test(SaveMeeting::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $meeting->unique_id)
        ->set('title', 'Updated Title')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('meeting-updated');

    expect($meeting->fresh()->title)->toBe('Updated Title');
});
