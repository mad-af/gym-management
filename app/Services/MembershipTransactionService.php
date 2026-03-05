<?php

namespace App\Services;

use App\Models\MembershipTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MembershipTransactionService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $customerId = null,
        ?string $status = null
    ): LengthAwarePaginator {
        $query = MembershipTransaction::query()
            ->with(['customer', 'package'])
            ->latest('created_at');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($status) {
            $query->where('status', $status);
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

    public function create(array $data, ?string $createdBy): MembershipTransaction
    {
        $payload = $data;

        if ($createdBy) {
            $payload['created_by'] = $createdBy;
        }

        return MembershipTransaction::create($payload)->refresh();
    }

    public function update(MembershipTransaction $transaction, array $data): MembershipTransaction
    {
        $transaction->update($data);

        return $transaction->refresh();
    }

    public function delete(MembershipTransaction $transaction): bool
    {
        return (bool) $transaction->delete();
    }
}
