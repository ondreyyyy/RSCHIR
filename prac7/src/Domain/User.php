<?php
declare(strict_types=1);

namespace App\Domain;

final class User
{
    public function __construct(
        public ?int $id,
        public string $username,
        public ?string $passwordHash = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
        ];
    }

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) ($row['username'] ?? '')
        );
    }
}
