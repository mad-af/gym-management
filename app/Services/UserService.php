<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll(int $perPage = 10, ?string $search = null, int $page = 1, ?string $roleName = null): LengthAwarePaginator
    {
        $query = User::with(['roles'])->latest();

        if ($roleName) {
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(int $perPage = 20, ?string $search = null, int $page = 1): LengthAwarePaginator
    {
        $query = User::query()->select(['id', 'name', 'email']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'), // Default password
            ]);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            // Generate Avatar
            try {
                $generator = new AvatarGenerator;
                $generator->generateAndSave($user->name, 'svg', $user);
            } catch (\Exception $e) {
                // Log error silently to not disrupt user creation
                \Illuminate\Support\Facades\Log::error('Avatar generation failed: '.$e->getMessage());
            }

            return $user->load(['roles']);
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            $user->update($updateData);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            return $user->load(['roles']);
        });
    }

    public function delete(User $user): bool
    {
        return $this->deactivate($user);
    }

    public function deactivate(User $user): bool
    {
        return true;
    }

    public function activate(User $user): bool
    {
        return true;
    }
}
