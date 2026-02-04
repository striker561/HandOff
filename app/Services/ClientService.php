<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use App\Enums\User\AccountRole;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Hash, RateLimiter};

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
        // Base Query
        $query = User::query()->where('role', AccountRole::CLIENT);
        $query = $this->applyFilters($query, $filters);
        return $this->paginateQuery($query);
    }

    public function createClient(array $data): User
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

        return $client;
    }

    public function resendInvitation(User $user): void
    {
        $tempPass = Str::random(12);

        $user->update([
            'password' => Hash::make($tempPass),
        ]);

        $this->sendInvitationEmail($user, $tempPass);
    }


    private function sendInvitationEmail(User $user, string $tempPass): void
    {
        $key = "invite:{$user->id}";

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            abort(429, "Invitation already sent recently. Try again in {$seconds}s.");
        }

        RateLimiter::hit($key, 120); // 1 attempt per 120 seconds
    }
}