<?php

use App\Enums\User\AccountRole;
use App\Models\Comment;
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
    $this->deliverable = Deliverable::factory()->create(['project_unique_id' => $this->project->unique_id]);
});

it('allows admins all comment abilities via before hook', function () {
    $comment = Comment::factory()->forDeliverable($this->deliverable)->external()->create([
        'user_unique_id' => $this->client->unique_id,
    ]);

    expect(Gate::forUser($this->admin)->allows('view', $comment))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('update', $comment))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('delete', $comment))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('createInternal', Comment::class))->toBeTrue()
        ->and(Gate::forUser($this->admin)->allows('viewInternal', Comment::class))->toBeTrue();
});

it('allows clients to view external comments on their project', function () {
    $comment = Comment::factory()->forDeliverable($this->deliverable)->external()->create([
        'user_unique_id' => $this->client->unique_id,
    ]);

    expect(Gate::forUser($this->client)->allows('view', $comment))->toBeTrue();
});

it('denies clients from viewing internal comments', function () {
    $comment = Comment::factory()->forDeliverable($this->deliverable)->internal()->create([
        'user_unique_id' => $this->admin->unique_id,
    ]);

    expect(Gate::forUser($this->client)->allows('view', $comment))->toBeFalse();
});

it('denies clients from viewing comments outside their project', function () {
    $comment = Comment::factory()->forDeliverable($this->deliverable)->external()->create([
        'user_unique_id' => $this->client->unique_id,
    ]);

    expect(Gate::forUser($this->otherClient)->allows('view', $comment))->toBeFalse();
});

it('allows clients to create comments on their project deliverables', function () {
    expect(Gate::forUser($this->client)->allows('create', [Comment::class, $this->deliverable]))->toBeTrue();
});

it('denies clients from creating comments outside their project', function () {
    expect(Gate::forUser($this->otherClient)->allows('create', [Comment::class, $this->deliverable]))->toBeFalse();
});

it('allows clients to update and delete their own external comments', function () {
    $comment = Comment::factory()->forDeliverable($this->deliverable)->external()->create([
        'user_unique_id' => $this->client->unique_id,
    ]);

    expect(Gate::forUser($this->client)->allows('update', $comment))->toBeTrue()
        ->and(Gate::forUser($this->client)->allows('delete', $comment))->toBeTrue();
});

it('denies clients from updating another users comments', function () {
    $comment = Comment::factory()->forDeliverable($this->deliverable)->external()->create([
        'user_unique_id' => $this->otherClient->unique_id,
    ]);

    expect(Gate::forUser($this->client)->allows('update', $comment))->toBeFalse();
});
