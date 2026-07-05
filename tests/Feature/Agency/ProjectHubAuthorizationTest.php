<?php

use App\Enums\Deliverable\DeliverableStatus;
use App\Enums\Meeting\MeetingStatus;
use App\Enums\User\AccountRole;
use App\Livewire\Agency\Projects\Credentials\SaveCredential;
use App\Livewire\Agency\Projects\Credentials\ViewCredential;
use App\Livewire\Agency\Projects\Deliverables\DeliverablesList;
use App\Livewire\Agency\Projects\Deliverables\SaveDeliverable;
use App\Livewire\Agency\Projects\Meetings\SaveMeeting;
use App\Livewire\Agency\Projects\Milestones\SaveMilestone;
use App\Models\Credential;
use App\Models\Deliverable;
use App\Models\Meeting;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $this->client = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->otherClient = User::factory()->create(['role' => AccountRole::CLIENT]);
    $this->project = Project::factory()->create(['client_unique_id' => $this->client->unique_id]);
    $this->milestone = Milestone::factory()->create(['project_unique_id' => $this->project->unique_id, 'order' => 1]);
});

describe('deliverables', function () {
    it('allows admins to create deliverables via save modal', function () {
        Livewire::actingAs($this->admin)
            ->test(SaveDeliverable::class)
            ->call('open', projectUniqueId: $this->project->unique_id, milestoneUniqueId: $this->milestone->unique_id)
            ->set('name', 'Policy Test Deliverable')
            ->set('milestone_unique_id', $this->milestone->unique_id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('deliverable-created');

        expect(Deliverable::query()->where('name', 'Policy Test Deliverable')->exists())->toBeTrue();
    });

    it('forbids clients from creating deliverables via save modal', function () {
        Livewire::actingAs($this->client)
            ->test(SaveDeliverable::class)
            ->call('open', projectUniqueId: $this->project->unique_id, milestoneUniqueId: $this->milestone->unique_id)
            ->set('name', 'Client Deliverable')
            ->set('milestone_unique_id', $this->milestone->unique_id)
            ->call('save')
            ->assertForbidden();
    });

    it('allows admins to open but forbids saving in-review deliverables', function () {
        $deliverable = Deliverable::factory()->create([
            'project_unique_id' => $this->project->unique_id,
            'milestone_unique_id' => $this->milestone->unique_id,
            'status' => DeliverableStatus::IN_REVIEW,
        ]);

        Livewire::actingAs($this->admin)
            ->test(SaveDeliverable::class)
            ->call('open', projectUniqueId: $this->project->unique_id, uniqueId: $deliverable->unique_id)
            ->assertSet('name', $deliverable->name)
            ->assertSet('readOnly', true)
            ->set('name', 'Blocked Update')
            ->call('save')
            ->assertHasNoErrors();

        expect($deliverable->fresh()->name)->toBe($deliverable->name);
    });

    it('forbids resubmitting in-review deliverables from the list', function () {
        $deliverable = Deliverable::factory()->create([
            'project_unique_id' => $this->project->unique_id,
            'milestone_unique_id' => $this->milestone->unique_id,
            'status' => DeliverableStatus::IN_REVIEW,
        ]);

        Livewire::actingAs($this->admin)
            ->test(DeliverablesList::class, ['projectUniqueId' => $this->project->unique_id])
            ->call('submitForReview', uniqueId: $deliverable->unique_id)
            ->assertForbidden();
    });
});

describe('credentials', function () {
    it('allows admins to create credentials via save modal', function () {
        Livewire::actingAs($this->admin)
            ->test(SaveCredential::class)
            ->call('open', projectUniqueId: $this->project->unique_id)
            ->set('name', 'Policy Credential')
            ->set('password', 'secret-password')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('credential-created');
    });

    it('forbids clients from creating credentials via save modal', function () {
        Livewire::actingAs($this->client)
            ->test(SaveCredential::class)
            ->call('open', projectUniqueId: $this->project->unique_id)
            ->set('name', 'Client Credential')
            ->set('password', 'secret-password')
            ->call('save')
            ->assertForbidden();
    });

    it('allows admins to update credentials via save modal', function () {
        $credential = Credential::factory()->create([
            'project_unique_id' => $this->project->unique_id,
            'password' => Crypt::encryptString('unchanged'),
        ]);

        Livewire::actingAs($this->admin)
            ->test(SaveCredential::class)
            ->call('open', projectUniqueId: $this->project->unique_id, uniqueId: $credential->unique_id)
            ->set('name', 'Renamed Credential')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('credential-updated');
    });

    it('forbids clients from updating credentials via save modal', function () {
        $credential = Credential::factory()->create([
            'project_unique_id' => $this->project->unique_id,
            'password' => Crypt::encryptString('unchanged'),
        ]);

        Livewire::actingAs($this->client)
            ->test(SaveCredential::class)
            ->call('open', projectUniqueId: $this->project->unique_id, uniqueId: $credential->unique_id)
            ->set('name', 'Client Rename Attempt')
            ->call('save')
            ->assertForbidden();
    });

    it('allows admins to reveal passwords via view modal', function () {
        $credential = Credential::factory()->create([
            'project_unique_id' => $this->project->unique_id,
            'password' => Crypt::encryptString('vault-secret'),
        ]);

        Livewire::actingAs($this->admin)
            ->test(ViewCredential::class)
            ->call('open', uniqueId: $credential->unique_id, projectUniqueId: $this->project->unique_id)
            ->call('revealPassword')
            ->assertSet('passwordRevealed', true)
            ->assertSet('revealedPassword', 'vault-secret');
    });

    it('forbids clients from revealing passwords via view modal', function () {
        $credential = Credential::factory()->create([
            'project_unique_id' => $this->project->unique_id,
            'password' => Crypt::encryptString('vault-secret'),
        ]);

        Livewire::actingAs($this->client)
            ->test(ViewCredential::class)
            ->call('open', uniqueId: $credential->unique_id, projectUniqueId: $this->project->unique_id)
            ->call('revealPassword')
            ->assertForbidden();
    });
});

describe('milestones', function () {
    it('allows admins to create milestones via save modal', function () {
        Livewire::actingAs($this->admin)
            ->test(SaveMilestone::class)
            ->call('open', projectUniqueId: $this->project->unique_id)
            ->set('name', 'Policy Milestone')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('milestone-created');
    });

    it('forbids clients from creating milestones via save modal', function () {
        Livewire::actingAs($this->client)
            ->test(SaveMilestone::class)
            ->call('open', projectUniqueId: $this->project->unique_id)
            ->set('name', 'Client Milestone')
            ->call('save')
            ->assertForbidden();
    });

    it('forbids clients from updating milestones via save modal', function () {
        $milestone = Milestone::factory()->create([
            'project_unique_id' => $this->project->unique_id,
            'order' => 2,
        ]);

        Livewire::actingAs($this->client)
            ->test(SaveMilestone::class)
            ->call('open', projectUniqueId: $this->project->unique_id, uniqueId: $milestone->unique_id)
            ->set('name', 'Client Update Attempt')
            ->call('save')
            ->assertForbidden();
    });
});

describe('meetings', function () {
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
});
