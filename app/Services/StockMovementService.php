<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $productId = null,
        ?string $type = null
    ): LengthAwarePaginator {
        $query = StockMovement::query()
            ->with(['product', 'creator'])
            ->latest('created_at');

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($type) {
            $query->where('type', strtoupper($type));
        }

        if ($search) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?string $createdBy = null): StockMovement
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);

            $type = $data['type'];
            $quantity = (int) $data['quantity'];

            if ($type === 'IN') {
                $product->stock += $quantity;
            } elseif ($type === 'OUT') {
                if ($product->stock < $quantity) {
                    abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Stok produk tidak mencukupi.');
                }
                $product->stock -= $quantity;
            } elseif ($type === 'ADJUSTMENT') {
                $product->stock = $quantity;
            }

            $product->save();

            $payload = $data;
            if ($createdBy) {
                $payload['created_by'] = $createdBy;
            }

            return StockMovement::create($payload)->refresh()->load(['product', 'creator']);
        });
    }

    public function delete(StockMovement $movement): bool
    {
        return (bool) $movement->delete();
    }
}
