<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1
    ): LengthAwarePaginator {
        $query = Product::query()->latest('created_at');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(
        int $perPage = 20,
        ?string $search = null,
        int $page = 1
    ): LengthAwarePaginator {
        $query = Product::query()->latest('created_at');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->select(['id', 'name', 'price', 'stock'])->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->refresh();
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }
}
