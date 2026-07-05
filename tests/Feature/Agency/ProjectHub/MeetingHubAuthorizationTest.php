<?php

use App\Enums\Meeting\MeetingStatus;
use App\Livewire\Agency\Projects\Meetings\SaveMeeting;
use App\Models\Meeting;
use Livewire\Livewire;

beforeEach(function () {
    bindProjectHubAuthorizationContext();
});

it('allows admins to schedule meetings via save modal', function () {
    Livewire::actingAs($this->admin)
        ->test(SaveMeeting::class)
        ->call('open', projectUniqueId: $this->project->unique_id)
        ->set('title', 'Policy Meeting')
        ->set('scheduled_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('meeting-scheduled');
});

it('allows project clients to schedule meetings via save modal per policy', function () {
    Livewire::actingAs($this->client)
        ->test(SaveMeeting::class)
        ->call('open', projectUniqueId: $this->project->unique_id)
        ->set('title', 'Client Meeting')
        ->set('scheduled_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('meeting-scheduled');
});

it('forbids clients from scheduling meetings outside their project', function () {
    Livewire::actingAs($this->otherClient)
        ->test(SaveMeeting::class)
        ->call('open', projectUniqueId: $this->project->unique_id)
        ->set('title', 'Outside Meeting')
        ->set('scheduled_at', now()->addDay()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertForbidden();
});

it('forbids clients from opening meetings outside their project', function () {
    $meeting = Meeting::factory()->create([
        'project_unique_id' => $this->project->unique_id,
        'status' => MeetingStatus::SCHEDULED,
        'scheduled_at' => now()->addDay(),
    ]);

    Livewire::actingAs($this->otherClient)
        ->test(SaveMeeting::class)
        ->call('open', projectUniqueId: $this->project->unique_id, uniqueId: $meeting->unique_id)
        ->assertForbidden();
});
