<?php

use App\Data\Meetings\SaveMeetingData;
use App\Enums\Meeting\MeetingAction;
use App\Enums\Meeting\MeetingStatus;
use App\Enums\User\AccountRole;
use App\Events\Meeting\MeetingEvent;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use App\Services\MeetingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(MeetingService::class);
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->project = Project::factory()->create();
});

it('schedules a meeting', function () {
    $meeting = $this->service->scheduleMeeting(SaveMeetingData::fromArray([
        'project_unique_id' => $this->project->unique_id,
        'title' => 'Sprint Review',
        'scheduled_at' => now()->addDays(3)->toDateTimeString(),
        'duration_minutes' => 30,
        'location' => 'meet',
    ]), $this->admin);

    expect($meeting)->toBeInstanceOf(Meeting::class)
        ->and($meeting->title)->toBe('Sprint Review')
        ->and($meeting->status)->toBe(MeetingStatus::SCHEDULED);
});

it('updates a scheduled meeting', function () {
    Event::fake([MeetingEvent::class]);

    $meeting = Meeting::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MeetingStatus::SCHEDULED,
        'title' => 'Original',
        'scheduled_at' => now()->addDay(),
    ]);

    $updated = $this->service->updateMeeting($meeting, SaveMeetingData::fromArray([
        'project_unique_id' => $this->project->unique_id,
        'title' => 'Updated',
        'scheduled_at' => $meeting->scheduled_at->toDateTimeString(),
        'duration_minutes' => 45,
        'location' => 'meet',
    ]), $this->admin);

    expect($updated->title)->toBe('Updated')
        ->and($updated->duration_minutes)->toBe(45);

    Event::assertDispatched(MeetingEvent::class, function (MeetingEvent $event) use ($meeting) {
        return $event->action === MeetingAction::UPDATED
            && $event->meeting->is($meeting);
    });
});

it('rejects updating a completed meeting', function () {
    $meeting = Meeting::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MeetingStatus::COMPLETED,
    ]);

    expect(fn () => $this->service->updateMeeting($meeting, SaveMeetingData::fromArray([
        'project_unique_id' => $this->project->unique_id,
        'title' => 'Nope',
        'scheduled_at' => now()->addDay()->toDateTimeString(),
        'duration_minutes' => 30,
        'location' => 'meet',
    ]), $this->admin))->toThrow(InvalidArgumentException::class);
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
