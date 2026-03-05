<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $customerId = null
    ): LengthAwarePaginator {
        $query = Sale::query()
            ->with(['customer', 'items.product'])
            ->latest('created_at');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($search) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data, ?string $createdBy): Sale
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $items = $data['items'] ?? [];
            $customerId = $data['customer_id'] ?? null;

            $sale = Sale::create([
                'customer_id' => $customerId,
                'total_amount' => 0,
                'created_by' => $createdBy,
            ]);

            $total = 0;

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];

                if ($product->stock < $quantity) {
                    abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Stok produk tidak mencukupi.');
                }

                $price = array_key_exists('price', $item) && $item['price'] !== null
                    ? (float) $item['price']
                    : (float) $product->price;

                $subtotal = $price * $quantity;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $product->stock -= $quantity;
                $product->save();

                $total += $subtotal;
            }

            $sale->total_amount = $total;
            $sale->save();

            return $sale->load(['customer', 'items.product']);
        });
    }

    public function delete(Sale $sale): bool
    {
        return (bool) $sale->delete();
    }
}
