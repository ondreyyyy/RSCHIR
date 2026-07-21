<?php

namespace App\Services;

use App\Domain\UserRepository;
use App\Models\User;
use RuntimeException;

final class UserService
{
    public function __construct(
        private UserRepository $repository
    ) {
    }

    /** @return User[] */
    public function all(): array
    {
        return $this->repository->all();
    }

    public function find(int $id): ?User
    {
        return $this->repository->find($id);
    }

    public function create(array $data): User
    {
        $validated = $this->validate($data, true);
        return $this->repository->create(new User(null, $validated['username'], $validated['password']));
    }

    public function update(int $id, array $data): User
    {
        $validated = $this->validate($data, false);
        $existing = $this->repository->find($id);
        if ($existing === null) {
            throw new RuntimeException('Пользователь не найден.', 404);
        }

        $password = $validated['password'] ?? $existing->passwordHash;
        return $this->repository->update(new User($id, $validated['username'], $password));
    }

    public function delete(int $id): void
    {
        if ($this->repository->find($id) === null) {
            throw new RuntimeException('Пользователь не найден.', 404);
        }
        $this->repository->delete($id);
    }

    public function verifyPassword(string $login, string $password): bool
    {
        $user = $this->repository->findByUsername($login);
        if ($user === null || $user->passwordHash === null) {
            return false;
        }

        $stored = $user->passwordHash;
        if (str_starts_with($stored, '{SHA}')) {
            $expected = substr($stored, 5);
            return base64_encode(sha1($password, true)) === $expected;
        }

        return password_verify($password, $stored);
    }

    private function validate(array $data, bool $requirePassword): array
    {
        $errors = [];
        if (empty($data['username'])) {
            $errors[] = 'Поле username обязательно.';
        }
        if ($requirePassword && empty($data['password'])) {
            $errors[] = 'Поле password обязательно.';
        }
        if ($errors) {
            throw new \InvalidArgumentException(implode(' ', $errors));
        }

        $validated = ['username' => trim((string) $data['username'])];
        if (isset($data['password']) && $data['password'] !== '') {
            $validated['password'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }

        return $validated;
    }
}
