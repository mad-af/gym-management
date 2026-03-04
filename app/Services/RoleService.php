<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleService
{
    public function getAll(int $perPage = 10, ?string $search = null, int $page = 1, ?bool $isActive = null): LengthAwarePaginator
    {
        $query = Role::with('permissions')->withCount('users')->latest();

        if (! is_null($isActive)) {
            $query->where('is_active', $isActive);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(int $perPage = 20, ?string $search = null, int $page = 1): LengthAwarePaginator
    {
        $query = Role::query()
            ->where('is_active', true)
            ->select(['id', 'name']);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'is_active' => true,
        ]);
        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $role->update(['name' => $data['name']]);
        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function delete(Role $role): bool
    {
        return $this->deactivate($role);
    }

    public function deactivate(Role $role): bool
    {
        return $role->update(['is_active' => false]);
    }

    public function activate(Role $role): bool
    {
        return $role->update(['is_active' => true]);
    }

    public function getGroupedPermissions(): array
    {
        return \App\Enums\Permission::grouped();
    }
}
