<?php

namespace App\Infrastructure;

use App\Domain\User;
use App\Domain\UserRepository;
use App\Models\User as UserModel;

final class EloquentUserRepository implements UserRepository
{
    public function all(): array
    {
        return array_map(
            static fn(UserModel $model): User => User::fromArray(['id' => $model->id, 'username' => $model->username]),
            UserModel::orderBy('id')->get()->all()
        );
    }

    public function find(int $id): ?User
    {
        $model = UserModel::find($id);
        if ($model === null) {
            return null;
        }

        return new User((int) $model->id, $model->username, $model->password);
    }

    public function findByUsername(string $username): ?User
    {
        $model = UserModel::where('username', $username)->first();
        if ($model === null) {
            return null;
        }

        return new User((int) $model->id, $model->username, $model->password);
    }

    public function create(User $user): User
    {
        $model = new UserModel();
        $model->username = $user->username;
        $model->password = $user->passwordHash;
        $model->save();

        return new User((int) $model->id, $model->username, $model->password);
    }

    public function update(User $user): User
    {
        $model = UserModel::find($user->id);
        if ($model === null) {
            throw new \RuntimeException('User not found.', 404);
        }

        $model->username = $user->username;
        if ($user->passwordHash !== null) {
            $model->password = $user->passwordHash;
        }
        $model->save();

        return $user;
    }

    public function delete(int $id): void
    {
        UserModel::destroy($id);
    }
}
