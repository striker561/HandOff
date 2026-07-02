<?php

use App\Enums\User\AccountRole;
use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use App\Services\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(CommentService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->project = Project::factory()->create();
});

it('creates a comment on a project', function () {
    $comment = $this->service->createComment(
        $this->project,
        $this->admin,
        'Looking good!',
    );

    expect($comment)->toBeInstanceOf(Comment::class)
        ->and($comment->body)->toBe('Looking good!');
});

it('creates an internal comment', function () {
    $comment = $this->service->createComment(
        $this->project,
        $this->admin,
        'Internal note',
        isInternal: true,
    );

    expect($comment->is_internal)->toBeTrue();
});

it('creates a reply to a comment', function () {
    $parent = $this->service->createComment(
        $this->project,
        $this->admin,
        'Parent comment',
    );

    $reply = $this->service->createReply(
        $parent,
        $this->admin,
        'This is a reply',
    );

    expect($reply->parent_unique_id)->toBe($parent->unique_id);
});

it('marks a comment as read', function () {
    $comment = $this->service->createComment(
        $this->project,
        $this->admin,
        'Read me',
    );

    $read = $this->service->markAsRead($comment);

    expect($read->read_at)->not->toBeNull();
});
