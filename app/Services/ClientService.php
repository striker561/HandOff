<?php

namespace App\Services;

use App\Enums\User\AccountRole;
use App\Enums\User\ClientAction;
use App\Events\User\ClientEvent;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return User::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'email'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'email', 'created_at', 'updated_at'];
    }

    public function getClients(array $filters = []): LengthAwarePaginator
    {
        $query = User::query()->clients();
        $query = $this->applyFilters($query, $filters);

        return $this->paginateQuery($query);
    }

    public function findClient(string $uniqueId): ?User
    {
        return User::query()
            ->clients()
            ->where('unique_id', $uniqueId)
            ->first();
    }

    public function createClient(array $data, User $performedBy): User
    {
        $tempPass = Str::random(12);

        /** @var User $client */
        $client = $this->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($tempPass),
            'role' => AccountRole::CLIENT,
        ]);

        $this->sendInvitationEmail($client, $tempPass);

        ClientEvent::dispatch(
            $client,
            ClientAction::CREATED,
            $performedBy,
            []
        );

        return $client;
    }

    public function resendInvitation(User $user, User $performedBy): void
    {
        if ($user->email_verified_at !== null) {
            $this->fieldError(
                'invitation',
                __('This client has already accepted their invitation.'),
            );
        }

        $tempPass = Str::random(12);

        $user->update([
            'password' => Hash::make($tempPass),
        ]);

        $this->sendInvitationEmail($user, $tempPass);

        ClientEvent::dispatch(
            $user,
            ClientAction::INVITATION_SENT,
            $performedBy,
            []
        );
    }

    private function sendInvitationEmail(User $user, string $tempPass): void
    {
        $key = "invite:{$user->id}";

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            $this->fieldError(
                'invitation',
                __('Invitation already sent recently. Try again in :seconds seconds.', ['seconds' => $seconds]),
            );
        }

        RateLimiter::hit($key, 120); // 1 attempt per 120 seconds
    }

    /**
     * @throws ValidationException
     */
    private function fieldError(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}
