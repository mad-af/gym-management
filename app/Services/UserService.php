<?php

namespace App\Services;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll(int $perPage = 10, ?string $search = null, int $page = 1, ?string $roleName = null, ?bool $isActive = null): LengthAwarePaginator
    {
        $query = User::with(['roles', 'employee', 'opds'])->latest();

        if ($roleName) {
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        if (! is_null($isActive)) {
            $query->where('is_active', $isActive);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(int $perPage = 20, ?string $search = null, int $page = 1): LengthAwarePaginator
    {
        $query = User::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'email']);

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
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make('password'), // Default password
                'employee_id' => $data['employee_id'] ?? null,
                'is_active' => true,
            ]);

            $hasAllOpds = (bool) ($data['has_all_opds'] ?? false);
            $user->has_all_opds = $hasAllOpds;
            $user->save();

            $this->syncUserOpds($user, $data['opd_ids'] ?? null, $hasAllOpds);

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

            return $user->load(['roles', 'employee', 'opds']);
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'employee_id' => $data['employee_id'] ?? null,
            ];

            if (array_key_exists('has_all_opds', $data)) {
                $updateData['has_all_opds'] = (bool) $data['has_all_opds'];
            }

            $user->update($updateData);

            $hasAllOpds = (bool) ($updateData['has_all_opds'] ?? $user->has_all_opds);
            $this->syncUserOpds($user, $data['opd_ids'] ?? null, $hasAllOpds);

            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            return $user->load(['roles', 'employee', 'opds']);
        });
    }

    public function delete(User $user): bool
    {
        return $this->deactivate($user);
    }

    public function deactivate(User $user): bool
    {
        return $user->update(['is_active' => false]);
    }

    public function activate(User $user): bool
    {
        return $user->update(['is_active' => true]);
    }

    protected function syncUserOpds(User $user, ?array $opdIds, bool $hasAllOpds): void
    {
        if ($hasAllOpds) {
            $allOpdIds = Opd::query()->pluck('id')->all();
            $user->opds()->sync($allOpdIds);

            return;
        }

        if (is_array($opdIds)) {
            $user->opds()->sync($opdIds);

            return;
        }

        $user->opds()->sync([]);
    }
}
