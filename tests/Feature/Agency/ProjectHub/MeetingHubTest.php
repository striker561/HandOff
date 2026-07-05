<?php

use App\Enums\Meeting\MeetingStatus;
use App\Livewire\Agency\Projects\Meetings\SaveMeeting;
use App\Models\Deliverable;
use App\Models\Meeting;
use App\Models\Milestone;
use Livewire\Livewire;

it('schedules a meeting for a project', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();

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
    ['admin' => $admin, 'project' => $project] = projectHubActors();
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

it('updates a scheduled meeting from the save modal', function () {
    ['admin' => $admin, 'project' => $project] = projectHubActors();
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
