<?php

namespace App\Services;

use App\Enums\User\AccountRole;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ClientService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return User::class;
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
        // SEND EMAILS 
    }
}