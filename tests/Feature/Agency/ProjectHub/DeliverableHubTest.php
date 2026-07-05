<?php

use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Deliverable\DeliverableType;
use App\Livewire\Agency\Projects\Deliverables\DeliverablesList;
use App\Livewire\Agency\Projects\Deliverables\SaveDeliverable;
use App\Livewire\Ui\FileUploader;
use App\Models\Deliverable;
use App\Models\DeliverableFile;
use App\Models\Milestone;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('filters deliverables by milestone query string on the deliverables page', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
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
    ['admin' => $admin, 'project' => $project] = projectHubActors();
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
    ['admin' => $admin, 'project' => $project] = projectHubActors();
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
    ['admin' => $admin, 'project' => $project] = projectHubActors();
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
    ['admin' => $admin, 'project' => $project] = projectHubActors();
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

it('updates a deliverable from the save modal', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
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

    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $file = UploadedFile::fake()->create('wireframes.pdf', 512, 'application/pdf');

    Livewire::actingAs($admin)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $project->unique_id, milestoneUniqueId: $milestone->unique_id)
        ->set('name', 'Wireframes PDF')
        ->set('type', DeliverableType::DESIGN->value)
        ->set('milestone_unique_id', $milestone->unique_id)
        ->set('pendingDeliverableFiles', [$file])
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

    ['admin' => $admin, 'project' => $project] = projectHubActors();
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

    ['admin' => $admin, 'project' => $project] = projectHubActors();
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

    ['admin' => $admin, 'project' => $project] = projectHubActors();
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

it('allows admins to delete rejected deliverables from the list', function () {
    Storage::fake('deliverables');

    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'status' => DeliverableStatus::REJECTED,
    ]);

    Livewire::actingAs($admin)
        ->test(DeliverablesList::class, ['projectUniqueId' => $project->unique_id])
        ->call('deleteDeliverable', uniqueId: $deliverable->unique_id)
        ->assertHasNoErrors();

    expect(Deliverable::query()->where('unique_id', $deliverable->unique_id)->exists())->toBeFalse();
});

it('shows pending design images in the deliverable modal', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $file = UploadedFile::fake()->image('design.jpg', 800, 600);

    Livewire::actingAs($admin)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $project->unique_id, milestoneUniqueId: $milestone->unique_id)
        ->set('type', DeliverableType::DESIGN->value)
        ->set('pendingDeliverableFiles', [$file])
        ->assertSee('design.jpg')
        ->assertSee(__('New uploads'));
});

it('clears a pending file upload from the uploader', function () {
    $file = UploadedFile::fake()->create('draft.pdf', 128, 'application/pdf');

    Livewire::test(FileUploader::class)
        ->set('state.pending', [$file])
        ->call('removePending', index: 0)
        ->assertSet('state.pending', []);
});

it('allows opening but forbids saving an in-review deliverable', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
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
        ->assertSet('readOnly', true)
        ->assertSee(__('View Deliverable'))
        ->set('name', 'Should Not Save')
        ->call('save')
        ->assertSet('name', 'Should Not Save');

    expect($deliverable->fresh()->name)->toBe('Locked Deliverable');
});

it('shows a view action for in-review deliverables on the list', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'name' => 'Submitted Asset',
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    $html = Livewire::actingAs($admin)
        ->test(DeliverablesList::class, ['projectUniqueId' => $project->unique_id])
        ->html();

    expect($html)
        ->toContain('wire:click="viewDeliverable')
        ->not->toContain('wire:click="editDeliverable');
});

it('allows admins to view in-review deliverable files with preview links', function () {
    Storage::fake('deliverables');

    ['admin' => $admin, 'project' => $project] = projectHubActors();
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'name' => 'Design Pack',
        'type' => DeliverableType::DESIGN,
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    $path = "deliverables/{$project->unique_id}/hero.jpg";
    Storage::disk('deliverables')->put($path, 'fake-image-content');

    $file = DeliverableFile::factory()->create([
        'deliverable_unique_id' => $deliverable->unique_id,
        'uploaded_by_unique_id' => $admin->unique_id,
        'original_filename' => 'hero.jpg',
        'file_path' => $path,
        'mime_type' => 'image/jpeg',
    ]);

    Livewire::actingAs($admin)
        ->test(SaveDeliverable::class)
        ->call('open', projectUniqueId: $project->unique_id, uniqueId: $deliverable->unique_id)
        ->assertSet('readOnly', true)
        ->assertSee('hero.jpg')
        ->assertSee(__('Open in new tab'))
        ->assertSee($file->showUrl($project->unique_id));

    $this->actingAs($admin)
        ->get($file->showUrl($project->unique_id))
        ->assertOk()
        ->assertHeader('content-type', 'image/jpeg');
});

it('forbids submitting an in-review deliverable for review again', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
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
