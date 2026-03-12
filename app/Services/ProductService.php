<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getStats(int $lowStockThreshold = 5): array
    {
        $total = Product::query()->count();
        $active = Product::query()->where('is_active', true)->count();
        $outOfStock = Product::query()
            ->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '<=', 0);
            })
            ->count();
        $lowStock = Product::query()
            ->whereNotNull('stock')
            ->where('stock', '>', 0)
            ->where('stock', '<=', $lowStockThreshold)
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'outOfStock' => $outOfStock,
            'lowStock' => $lowStock,
        ];
    }

    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?bool $isActive = null
    ): LengthAwarePaginator {
        $query = Product::query()
            ->with(['media'])
            ->latest('created_at');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(
        int $perPage = 20,
        ?string $search = null,
        int $page = 1,
        bool $isActive = true
    ): LengthAwarePaginator {
        $query = Product::query()->latest('created_at');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query
            ->where('is_active', $isActive)
            ->select(['id', 'name', 'price', 'stock'])
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            if (! array_key_exists('is_active', $data)) {
                $data['is_active'] = true;
            }

            $product = Product::create($data);

            if (isset($data['stock']) && $data['stock'] > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'IN',
                    'quantity' => $data['stock'],
                    'description' => 'Initial stock',
                ]);
            }

            return $product->load(['media']);
        });
    }

    public function update(Product $product, array $data): Product
    {
        unset($data['stock']);
        $product->update($data);

        return $product->refresh()->load(['media']);
    }

    public function delete(Product $product): bool
    {
        return $product->update(['is_active' => false]);
    }

    public function activate(Product $product): bool
    {
        return $product->update(['is_active' => true]);
    }
}
