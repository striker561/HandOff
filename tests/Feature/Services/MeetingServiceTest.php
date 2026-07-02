<?php

use App\Enums\Meeting\MeetingStatus;
use App\Enums\User\AccountRole;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use App\Services\MeetingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(MeetingService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->project = Project::factory()->create();
});

it('schedules a meeting', function () {
    $meeting = $this->service->scheduleMeeting([
        'project_unique_id' => $this->project->unique_id,
        'title' => 'Sprint Review',
        'scheduled_at' => now()->addDays(3),
        'duration_minutes' => 30,
    ], $this->admin);

    expect($meeting)->toBeInstanceOf(Meeting::class)
        ->and($meeting->title)->toBe('Sprint Review')
        ->and($meeting->status)->toBe(MeetingStatus::SCHEDULED);
});

it('completes a meeting', function () {
    $meeting = Meeting::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MeetingStatus::SCHEDULED,
    ]);

    $completed = $this->service->completeMeeting($meeting, $this->admin, 'Good discussion');

    expect($completed->status)->toBe(MeetingStatus::COMPLETED);
});

it('cancels a meeting', function () {
    $meeting = Meeting::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MeetingStatus::SCHEDULED,
    ]);

    $cancelled = $this->service->cancelMeeting($meeting, $this->admin);

    expect($cancelled->status)->toBe(MeetingStatus::CANCELLED);
});

it('gets meetings for a project', function () {
    Meeting::factory()->count(2)->create([
        'project_unique_id' => $this->project->unique_id,
    ]);

    $result = $this->service->getMeetingsForProject($this->project->unique_id);

    expect($result->total())->toBe(2);
});
