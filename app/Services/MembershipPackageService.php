<?php

namespace App\Services;

use App\Models\MembershipPackage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MembershipPackageService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?bool $isActive = null
    ): LengthAwarePaginator {
        $query = MembershipPackage::query()->latest('created_at');

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(
        int $perPage = 20,
        ?string $search = null,
        int $page = 1,
        ?bool $isActive = true
    ): LengthAwarePaginator {
        $query = MembershipPackage::query()->latest('created_at');

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->select(['id', 'name'])->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): MembershipPackage
    {
        return MembershipPackage::create($data);
    }

    public function update(MembershipPackage $package, array $data): MembershipPackage
    {
        $package->update($data);

        return $package->refresh();
    }

    public function delete(MembershipPackage $package): bool
    {
        return (bool) $package->delete();
    }
}
