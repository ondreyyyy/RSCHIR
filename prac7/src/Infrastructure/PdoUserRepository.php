<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\User;
use App\Domain\UserRepository;
use PDO;

final class PdoUserRepository implements UserRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT id, username FROM users ORDER BY id');

        return array_map(
            static fn(array $row): User => User::fromArray($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function find(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT id, username FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? User::fromArray($row) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->pdo->prepare('SELECT id, username, password FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? User::fromArray($row) : null;
    }

    public function create(User $user): User
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
        $stmt->execute([
            ':username' => $user->username,
            ':password' => $user->passwordHash,
        ]);

        return new User((int) $this->pdo->lastInsertId(), $user->username, $user->passwordHash);
    }

    public function update(User $user): User
    {
        $sql = 'UPDATE users SET username = :username';
        $params = [':username' => $user->username, ':id' => $user->id];

        if ($user->passwordHash !== null) {
            $sql .= ', password = :password';
            $params[':password'] = $user->passwordHash;
        }
        $sql .= ' WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $user;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
