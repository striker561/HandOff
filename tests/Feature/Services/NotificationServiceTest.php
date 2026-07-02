<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Notification;
use App\Enums\User\AccountRole;
use App\Enums\Notification\NotificationType;
use App\Services\NotificationService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(NotificationService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);
});

it('creates a notification', function () {
    $notification = $this->service->createNotification(
        $this->client,
        NotificationType::DELIVERABLE,
        $this->project,
        ['message' => 'Your deliverable is ready'],
    );

    expect($notification)->toBeInstanceOf(Notification::class)
        ->and($notification->type)->toBe(NotificationType::DELIVERABLE);
});

it('gets unread count', function () {
    Notification::factory()->count(3)->create([
        'user_unique_id' => $this->client->unique_id,
        'read_at' => null,
    ]);

    $count = $this->service->getUnreadCount($this->client);

    expect($count)->toBe(3);
});

it('marks a notification as read', function () {
    $notification = Notification::factory()->create([
        'user_unique_id' => $this->client->unique_id,
        'read_at' => null,
    ]);

    $read = $this->service->markAsRead($notification);

    expect($read->read_at)->not->toBeNull();
});

it('marks all notifications as read', function () {
    Notification::factory()->count(3)->create([
        'user_unique_id' => $this->client->unique_id,
        'read_at' => null,
    ]);

    $count = $this->service->markAllAsRead($this->client);

    expect($count)->toBe(3);
});
