<?php

use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\User\AccountRole;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->otherClient = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);
});

function deliverableForProject(Project $project, DeliverableStatus $status): Deliverable
{
    return Deliverable::factory()->create([
        'project_unique_id' => $project->unique_id,
        'status' => $status,
    ]);
}

it('allows admins to update draft and rejected deliverables', function (DeliverableStatus $status) {
    $deliverable = deliverableForProject($this->project, $status);

    expect(Gate::forUser($this->admin)->allows('update', $deliverable))->toBeTrue();
})->with([
    DeliverableStatus::DRAFT,
    DeliverableStatus::REJECTED,
]);

it('denies admins from updating non-editable deliverables', function (DeliverableStatus $status) {
    $deliverable = deliverableForProject($this->project, $status);

    expect(Gate::forUser($this->admin)->allows('update', $deliverable))->toBeFalse();
})->with([
    DeliverableStatus::IN_REVIEW,
    DeliverableStatus::APPROVED,
]);

it('allows admins to submit editable deliverables for review', function (DeliverableStatus $status) {
    $deliverable = deliverableForProject($this->project, $status);

    expect(Gate::forUser($this->admin)->allows('submitForReview', $deliverable))->toBeTrue();
})->with([
    DeliverableStatus::DRAFT,
    DeliverableStatus::REJECTED,
]);

it('denies submit for review when deliverable is not agency editable', function (DeliverableStatus $status) {
    $deliverable = deliverableForProject($this->project, $status);

    expect(Gate::forUser($this->admin)->allows('submitForReview', $deliverable))->toBeFalse();
})->with([
    DeliverableStatus::IN_REVIEW,
    DeliverableStatus::APPROVED,
]);

it('allows project clients to approve and reject in-review deliverables', function (string $ability) {
    $deliverable = deliverableForProject($this->project, DeliverableStatus::IN_REVIEW);

    expect(Gate::forUser($this->client)->allows($ability, $deliverable))->toBeTrue();
})->with(['approve', 'reject']);

it('denies admins from approving or rejecting deliverables', function (string $ability) {
    $deliverable = deliverableForProject($this->project, DeliverableStatus::IN_REVIEW);

    expect(Gate::forUser($this->admin)->allows($ability, $deliverable))->toBeFalse();
})->with(['approve', 'reject']);

it('denies clients from reviewing deliverables outside their project', function (string $ability) {
    $deliverable = deliverableForProject($this->project, DeliverableStatus::IN_REVIEW);

    expect(Gate::forUser($this->otherClient)->allows($ability, $deliverable))->toBeFalse();
})->with(['approve', 'reject']);

it('denies clients from reviewing non-reviewable deliverables', function (string $ability, DeliverableStatus $status) {
    $deliverable = deliverableForProject($this->project, $status);

    expect(Gate::forUser($this->client)->allows($ability, $deliverable))->toBeFalse();
})->with([
    ['approve', DeliverableStatus::DRAFT],
    ['approve', DeliverableStatus::APPROVED],
    ['reject', DeliverableStatus::DRAFT],
    ['reject', DeliverableStatus::APPROVED],
]);

it('allows admins to upload files only for agency editable deliverables', function (DeliverableStatus $status, bool $allowed) {
    $deliverable = deliverableForProject($this->project, $status);

    expect(Gate::forUser($this->admin)->allows('uploadFile', $deliverable))->toBe($allowed);
})->with([
    [DeliverableStatus::DRAFT, true],
    [DeliverableStatus::REJECTED, true],
    [DeliverableStatus::IN_REVIEW, false],
    [DeliverableStatus::APPROVED, false],
]);
