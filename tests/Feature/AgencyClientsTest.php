<?php

use App\Enums\User\AccountRole;
use App\Livewire\Agency\Clients\ClientsList;
use App\Livewire\Agency\Clients\SaveClient;
use App\Livewire\Agency\Clients\ViewClient;
use App\Models\User;
use App\Services\ClientService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

it('loads the clients page for admins', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    $this->actingAs($admin)
        ->get(route('agency.clients.index'))
        ->assertSuccessful()
        ->assertSee(__('Clients'))
        ->assertSeeLivewire(ClientsList::class)
        ->assertSeeLivewire(SaveClient::class)
        ->assertSeeLivewire(ViewClient::class);
});

it('forbids client users from the agency clients page', function () {
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    $this->actingAs($client)
        ->get(route('agency.clients.index'))
        ->assertForbidden();
});

it('creates a client from the modal', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);

    Livewire::actingAs($admin)
        ->test(SaveClient::class)
        ->set('name', 'New Client')
        ->set('email', 'newclient@gmail.com')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('client-created');

    expect(User::query()->where('email', 'newclient@gmail.com')->exists())->toBeTrue();
});

it('filters clients when search is updated', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    User::factory()->create(['name' => 'John Doe', 'role' => AccountRole::CLIENT]);
    User::factory()->create(['name' => 'Jane Smith', 'role' => AccountRole::CLIENT]);

    Livewire::actingAs($admin)
        ->test(ClientsList::class)
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

it('dispatches open-client-view with the client unique id', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    Livewire::actingAs($admin)
        ->test(ClientsList::class)
        ->call('viewClient', $client->unique_id)
        ->assertDispatched('open-client-view', uniqueId: $client->unique_id);
});

it('loads client details when opened by unique id', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create([
        'name' => 'View Me',
        'email' => 'view@example.com',
        'role' => AccountRole::CLIENT,
    ]);

    Livewire::actingAs($admin)
        ->test(ViewClient::class)
        ->call('open', uniqueId: $client->unique_id)
        ->assertSet('uniqueId', $client->unique_id)
        ->assertSet('name', 'View Me')
        ->assertSet('email', 'view@example.com')
        ->assertSet('status', __('Active'))
        ->assertSet('isInvited', false);
});

it('denies resend invitation for active clients', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    expect(Gate::forUser($admin)->allows('resendInvitation', $client))->toBeFalse();
});

it('allows resend invitation for invited clients', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->unverified()->create(['role' => AccountRole::CLIENT]);

    expect(Gate::forUser($admin)->allows('resendInvitation', $client))->toBeTrue();
});

it('resends invitation for invited clients from the view flyout', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->unverified()->create([
        'name' => 'Invited Client',
        'role' => AccountRole::CLIENT,
    ]);

    Livewire::actingAs($admin)
        ->test(ViewClient::class)
        ->call('open', uniqueId: $client->unique_id)
        ->call('resendInvitation')
        ->assertHasNoErrors();
});

it('shows resend invitation in the view flyout for invited clients', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->unverified()->create(['role' => AccountRole::CLIENT]);

    Livewire::actingAs($admin)
        ->test(ViewClient::class)
        ->call('open', uniqueId: $client->unique_id)
        ->assertSee(__('Resend invitation'));
});

it('surfaces invitation rate limits as field errors in the view flyout', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->unverified()->create(['role' => AccountRole::CLIENT]);

    app(ClientService::class)->resendInvitation($client, $admin);

    Livewire::actingAs($admin)
        ->test(ViewClient::class)
        ->call('open', uniqueId: $client->unique_id)
        ->call('resendInvitation')
        ->assertHasErrors(['invitation']);
});

it('throws validation exceptions from the service for invalid resend', function () {
    $admin = User::factory()->create(['role' => AccountRole::ADMIN]);
    $client = User::factory()->create(['role' => AccountRole::CLIENT]);

    expect(fn() => app(ClientService::class)->resendInvitation($client, $admin))
        ->toThrow(ValidationException::class);
});
