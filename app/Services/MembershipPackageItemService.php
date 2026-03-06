<?php

namespace App\Services;

use App\Models\MembershipPackageItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MembershipPackageItemService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $packageId = null
    ): LengthAwarePaginator {
        $query = MembershipPackageItem::query()
            ->with(['package'])
            ->latest('id');

        if ($packageId) {
            $query->where('package_id', $packageId);
        }

        if ($search) {
            $query->where('item_name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): MembershipPackageItem
    {
        return MembershipPackageItem::create($data);
    }

    public function update(MembershipPackageItem $item, array $data): MembershipPackageItem
    {
        $item->update($data);

        return $item->refresh();
    }

    public function delete(MembershipPackageItem $item): bool
    {
        return (bool) $item->delete();
    }
}
