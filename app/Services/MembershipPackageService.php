<?php

namespace App\Services;

use App\Models\MembershipPackage;
use App\Models\MembershipPackageItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MembershipPackageService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?bool $isActive = null
    ): LengthAwarePaginator {
        $query = MembershipPackage::query()->with(['items', 'media'])->latest('created_at');
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
        $items = $data['items'] ?? null;
        unset($data['items']);

        return DB::transaction(function () use ($data, $items) {
            $package = MembershipPackage::create($data);

            if (is_array($items) && count($items) > 0) {
                $package->items()->createMany(
                    collect($items)
                        ->map(fn (array $item) => [
                            'item_name' => $item['item_name'],
                            'quantity' => $item['quantity'],
                            'unit' => array_key_exists('unit', $item) && $item['unit'] !== '' ? $item['unit'] : null,
                        ])
                        ->all()
                );
            }

            return $package->load(['items', 'media']);
        });
    }

    public function update(MembershipPackage $package, array $data): MembershipPackage
    {
        $itemsProvided = array_key_exists('items', $data);
        $items = $data['items'] ?? null;
        unset($data['items']);

        return DB::transaction(function () use ($package, $data, $itemsProvided, $items) {
            $package->update($data);

            if ($itemsProvided) {
                $incomingItems = is_array($items) ? $items : [];
                $incomingIdsCandidate = collect($incomingItems)->pluck('id')->filter()->values()->all();
                $incomingIds = count($incomingIdsCandidate) > 0
                    ? MembershipPackageItem::query()
                        ->where('package_id', $package->id)
                        ->whereIn('id', $incomingIdsCandidate)
                        ->pluck('id')
                        ->all()
                    : [];

                if (count($incomingIds) === 0) {
                    $package->items()->delete();
                } else {
                    $package->items()->whereNotIn('id', $incomingIds)->delete();
                }

                foreach ($incomingItems as $item) {
                    $payload = [
                        'item_name' => $item['item_name'],
                        'quantity' => $item['quantity'],
                        'unit' => array_key_exists('unit', $item) && $item['unit'] !== '' ? $item['unit'] : null,
                    ];

                    if (! empty($item['id'])) {
                        $existing = MembershipPackageItem::query()
                            ->where('package_id', $package->id)
                            ->whereKey($item['id'])
                            ->first();

                        if ($existing) {
                            $existing->update($payload);
                        }
                    } else {
                        $package->items()->create($payload);
                    }
                }
            }

            return $package->load(['items', 'media']);
        });
    }

    public function delete(MembershipPackage $package): bool
    {
        return $package->update(['is_active' => false]);
    }

    public function activate(MembershipPackage $package): bool
    {
        return $package->update(['is_active' => true]);
    }
}
