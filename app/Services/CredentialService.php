<?php

namespace App\Services;

use App\Data\Credentials\SaveCredentialData;
use App\Enums\Credential\CredentialAction;
use App\Enums\Credential\CredentialType;
use App\Events\Credential\CredentialEvent;
use App\Models\Credential;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class CredentialService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return Credential::class;
    }

    protected function searchableColumns(): array
    {
        return ['name'];
    }

    protected function filterableColumns(): array
    {
        return ['project_unique_id', 'type'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'type', 'created_at', 'updated_at', 'last_accessed_at'];
    }

    public function findCredentialForProject(string $uniqueId, string $projectUniqueId): ?Credential
    {
        return Credential::query()
            ->where('unique_id', $uniqueId)
            ->where('project_unique_id', $projectUniqueId)
            ->first();
    }

    public function createCredential(SaveCredentialData $data, User $performedBy): Credential
    {
        if ($data->password === null) {
            throw new \InvalidArgumentException('Password is required when creating a credential.');
        }

        /** @var Credential $credential */
        $credential = $this->create($data->toCreateAttributes());

        CredentialEvent::dispatch(
            $credential,
            CredentialAction::CREATED,
            $performedBy,
            []
        );

        return $credential;
    }

    public function updateCredential(Credential $credential, SaveCredentialData $data, User $performedBy): Credential
    {
        $updateData = $data->toUpdateAttributes();

        if ($data->password !== null) {
            $updateData['password'] = $data->password;
        }

        $credential->update($updateData);

        CredentialEvent::dispatch(
            $credential,
            CredentialAction::UPDATED,
            $performedBy,
            []
        );

        return $credential->fresh();
    }

    public function getCredentialsForProject(string $projectUniqueId, array $filters = []): LengthAwarePaginator
    {
        $query = Credential::query()
            ->where('project_unique_id', $projectUniqueId)
            ->select([
                'unique_id',
                'project_unique_id',
                'name',
                'type',
                'metadata',
                'last_accessed_at',
                'created_at',
                'updated_at',
            ]);
        $query = $this->applyFilters($query, $filters);

        return $this->paginateQuery($query, $filters);
    }

    /**
     * @return array{
     *     unique_id: string,
     *     name: string,
     *     type: string,
     *     username: string|null,
     *     password: string,
     *     url: string|null,
     *     notes: string|null,
     *     last_accessed_at: Carbon|null,
     *     accessed_by: string
     * }
     */
    public function revealCredential(Credential $credential, User $user): array
    {
        $credential->update(['last_accessed_at' => now()]);

        $credential = $credential->fresh();

        CredentialEvent::dispatch(
            $credential,
            CredentialAction::ACCESSED,
            $user,
            []
        );

        return [
            'unique_id' => $credential->unique_id,
            'name' => $credential->name,
            'type' => $credential->type instanceof CredentialType ? $credential->type->value : $credential->type,
            'username' => $credential->username,
            'password' => $credential->password ?? '',
            'url' => $credential->url,
            'notes' => $credential->notes,
            'last_accessed_at' => $credential->last_accessed_at,
            'accessed_by' => $user->name,
        ];
    }
}
