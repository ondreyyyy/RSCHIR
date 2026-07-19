<?php
declare(strict_types=1);

namespace App\Domain;

interface UserRepository
{
    /** @return User[] */
    public function all(): array;

    public function find(int $id): ?User;

    public function findByUsername(string $username): ?User;

    public function create(User $user): User;

    public function update(User $user): User;

    public function delete(int $id): void;
}
