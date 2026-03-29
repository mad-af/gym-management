<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockMovementService
{
    public function getStats(): array
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $movementsThisMonth = StockMovement::query()
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $endOfMonth)
            ->count();

        $inThisMonth = (int) StockMovement::query()
            ->where('type', 'IN')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $endOfMonth)
            ->sum('quantity');

        $outThisMonth = (int) StockMovement::query()
            ->where('type', 'OUT')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $endOfMonth)
            ->sum('quantity');

        return [
            'movementsThisMonth' => $movementsThisMonth,
            'inThisMonth' => $inThisMonth,
            'outThisMonth' => $outThisMonth,
        ];
    }

    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $productId = null,
        ?string $type = null,
        ?string $createdBy = null,
        ?string $startDate = null,
        ?string $endDate = null
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

        if ($createdBy) {
            $query->where('created_by', $createdBy);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
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

            $type = strtoupper((string) $data['type']);
            $quantity = (int) $data['quantity'];

            if ($type === 'IN') {
                $product->stock += $quantity;
            } elseif ($type === 'ADJUSTMENT') {
                $product->stock = $quantity;
            } else {
                throw new InvalidArgumentException('Tipe pergerakan stok tidak valid.');
            }

            $product->save();

            $payload = [
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $quantity,
                'description' => $data['description'] ?? null,
            ];
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

    public function getExportData(string $startDate, string $endDate): Collection
    {
        return StockMovement::query()
            ->with(['product', 'creator'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at')
            ->get();
    }
}
