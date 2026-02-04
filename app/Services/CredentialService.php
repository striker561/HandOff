<?php

namespace App\Services;

use App\Models\{Credential, User};
use Illuminate\Support\Facades\Crypt;
use Illuminate\Pagination\LengthAwarePaginator;

class CredentialService extends BaseCRUDService
{
    protected function getModel(): string
    {
        return Credential::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'username', 'url'];
    }

    protected function filterableColumns(): array
    {
        return ['project_unique_id', 'type'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'type', 'created_at', 'updated_at', 'last_accessed_at'];
    }

    public function createCredential(array $data): Credential
    {
        /** @var Credential $credential */
        $credential = $this->create([
            'project_unique_id' => $data['project_unique_id'],
            'name' => $data['name'],
            'type' => $data['type'],
            'username' => $data['username'] ?? null,
            'password' => $this->encryptPassword($data['password']),
            'url' => $data['url'] ?? null,
            'notes' => $data['notes'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ]);
        return $credential;
    }

    public function updateCredential(Credential $credential, array $data): Credential
    {
        $updateData = [
            'name' => $data['name'] ?? $credential->name,
            'type' => $data['type'] ?? $credential->type,
            'username' => $data['username'] ?? $credential->username,
            'url' => $data['url'] ?? $credential->url,
            'notes' => $data['notes'] ?? $credential->notes,
            'metadata' => $data['metadata'] ?? $credential->metadata,
        ];

        // Only update password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = $this->encryptPassword($data['password']);
        }

        $credential->update($updateData);

        return $credential->fresh();
    }

    public function getCredentialsForProject(string $projectUniqueId, array $filters = []): LengthAwarePaginator
    {
        $query = Credential::query()->where('project_unique_id', $projectUniqueId);
        $query = $this->applyFilters($query, $filters);
        return $this->paginateQuery($query, $filters);
    }

    public function revealCredential(Credential $credential, User $user): array
    {
        // Track access
        $credential->update(['last_accessed_at' => now()]);

        return [
            'unique_id' => $credential->unique_id,
            'name' => $credential->name,
            'type' => $credential->type->value,
            'username' => $credential->username,
            'password' => $this->decryptPassword($credential),
            'url' => $credential->url,
            'notes' => $credential->notes,
            'last_accessed_at' => $credential->last_accessed_at,
            'accessed_by' => $user->name,
        ];
    }

    private function encryptPassword(string $password): string
    {
        return Crypt::encryptString($password);
    }

    private function decryptPassword(Credential $credential): string
    {
        try {
            return Crypt::decryptString($credential->password);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to decrypt credential password');
        }
    }
}
