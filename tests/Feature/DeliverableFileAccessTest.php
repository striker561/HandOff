<?php

use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Deliverable\DeliverableType;
use App\Enums\User\AccountRole;
use App\Models\Deliverable;
use App\Models\DeliverableFile;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

it('registers deliverable file routes outside the agency prefix', function () {
    expect(route('projects.deliverables.files.show', [
        'projectUniqueId' => '00000000-0000-4000-8000-000000000001',
        'deliverableUniqueId' => '00000000-0000-4000-8000-000000000002',
        'fileUniqueId' => '00000000-0000-4000-8000-000000000003',
    ]))->toBe(
        url('/projects/00000000-0000-4000-8000-000000000001/deliverables/00000000-0000-4000-8000-000000000002/files/00000000-0000-4000-8000-000000000003'),
    );
});

it('allows admins to stream deliverable files on shared project routes', function () {
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
        'status' => DeliverableStatus::IN_REVIEW,
    ]);

    $path = "deliverables/{$project->unique_id}/brief.pdf";
    Storage::disk('deliverables')->put($path, 'pdf-content');

    $file = DeliverableFile::factory()->create([
        'deliverable_unique_id' => $deliverable->unique_id,
        'uploaded_by_unique_id' => $admin->unique_id,
        'original_filename' => 'brief.pdf',
        'file_path' => $path,
        'mime_type' => 'application/pdf',
    ]);

    $this->actingAs($admin)
        ->get($file->showUrl($project->unique_id))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('allows project clients to stream submitted deliverable files', function () {
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
        'status' => DeliverableStatus::APPROVED,
    ]);

    $path = "deliverables/{$project->unique_id}/handoff.zip";
    Storage::disk('deliverables')->put($path, 'zip-content');

    $file = DeliverableFile::factory()->create([
        'deliverable_unique_id' => $deliverable->unique_id,
        'uploaded_by_unique_id' => $admin->unique_id,
        'original_filename' => 'handoff.zip',
        'file_path' => $path,
        'mime_type' => 'application/zip',
    ]);

    $this->actingAs($client)
        ->get($file->showUrl($project->unique_id))
        ->assertOk()
        ->assertHeader('content-type', 'application/zip');
});

it('forbids clients from streaming draft deliverable files', function () {
    Storage::fake('deliverables');

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

    $path = "deliverables/{$project->unique_id}/draft.pdf";
    Storage::disk('deliverables')->put($path, 'draft-content');

    $file = DeliverableFile::factory()->create([
        'deliverable_unique_id' => $deliverable->unique_id,
        'uploaded_by_unique_id' => $admin->unique_id,
        'file_path' => $path,
    ]);

    expect(Gate::forUser($client)->allows('download', $file))->toBeFalse();

    $this->actingAs($client)
        ->get($file->showUrl($project->unique_id))
        ->assertForbidden();
});

it('returns not found for deliverable files outside the scoped project', function () {
    Storage::fake('deliverables');

    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $otherProject = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'status' => DeliverableStatus::APPROVED,
    ]);

    $path = "deliverables/{$project->unique_id}/missing.pdf";
    Storage::disk('deliverables')->put($path, 'content');

    $file = DeliverableFile::factory()->create([
        'deliverable_unique_id' => $deliverable->unique_id,
        'uploaded_by_unique_id' => $admin->unique_id,
        'file_path' => $path,
    ]);

    $this->actingAs($admin)
        ->get(route('projects.deliverables.files.show', [
            'projectUniqueId' => $otherProject->unique_id,
            'deliverableUniqueId' => $deliverable->unique_id,
            'fileUniqueId' => $file->unique_id,
        ]))
        ->assertNotFound();
});

it('rejects invalid disposition query values', function () {
    Storage::fake('deliverables');

    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'status' => DeliverableStatus::APPROVED,
    ]);

    $path = "deliverables/{$project->unique_id}/file.pdf";
    Storage::disk('deliverables')->put($path, 'content');

    $file = DeliverableFile::factory()->create([
        'deliverable_unique_id' => $deliverable->unique_id,
        'uploaded_by_unique_id' => $admin->unique_id,
        'file_path' => $path,
    ]);

    $this->actingAs($admin)
        ->from($file->showUrl($project->unique_id))
        ->get($file->showUrl($project->unique_id).'?disposition=download')
        ->assertInvalid(['disposition']);
});

it('forbids clients from streaming deliverable files on other projects', function () {
    Storage::fake('deliverables');

    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $otherClient = User::factory()->create(['role' => AccountRole::CLIENT]);
    $project = Project::factory()->create(['client_unique_id' => $client->unique_id]);
    $milestone = Milestone::factory()->create(['project_unique_id' => $project->unique_id, 'order' => 1]);
    $deliverable = Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'milestone_unique_id' => $milestone->unique_id,
        'created_by_unique_id' => $admin->unique_id,
        'status' => DeliverableStatus::APPROVED,
    ]);

    $path = "deliverables/{$project->unique_id}/private.pdf";
    Storage::disk('deliverables')->put($path, 'private-content');

    $file = DeliverableFile::factory()->create([
        'deliverable_unique_id' => $deliverable->unique_id,
        'uploaded_by_unique_id' => $admin->unique_id,
        'file_path' => $path,
    ]);

    $this->actingAs($otherClient)
        ->get($file->showUrl($project->unique_id))
        ->assertForbidden();
});
