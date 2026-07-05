<?php

namespace App\Data\Credentials;

use App\Enums\Credential\CredentialType;
use SensitiveParameter;

readonly class SaveCredentialData
{
    public function __construct(
        public string $projectUniqueId,
        public string $name,
        public CredentialType $type,
        #[SensitiveParameter]
        public ?string $username,
        #[SensitiveParameter]
        public ?string $password,
        #[SensitiveParameter]
        public ?string $url,
        #[SensitiveParameter]
        public ?string $notes,
        public array $metadata = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            projectUniqueId: $data['project_unique_id'],
            name: $data['name'],
            type: CredentialType::from($data['type']),
            username: isset($data['username']) && $data['username'] !== '' ? $data['username'] : null,
            password: isset($data['password']) && $data['password'] !== '' ? $data['password'] : null,
            url: isset($data['url']) && $data['url'] !== '' ? $data['url'] : null,
            notes: isset($data['notes']) && $data['notes'] !== '' ? $data['notes'] : null,
            metadata: $data['metadata'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toCreateAttributes(): array
    {
        return [
            'project_unique_id' => $this->projectUniqueId,
            'name' => $this->name,
            'type' => $this->type,
            'username' => $this->username,
            'password' => $this->password,
            'url' => $this->url,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toUpdateAttributes(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'username' => $this->username,
            'url' => $this->url,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
        ];
    }
}
