<?php

namespace App\Services;

use App\Models\MembershipPackage;
use App\Models\MembershipTransaction;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MembershipTransactionService
{
    public function getStats(): array
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $activeMembers = MembershipTransaction::query()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->distinct('customer_id')
            ->count('customer_id');

        $transactionsThisMonth = MembershipTransaction::query()
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $endOfMonth)
            ->count();

        $revenueThisMonth = (float) MembershipTransaction::query()
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $endOfMonth)
            ->sum('price');

        return [
            'activeMembers' => $activeMembers,
            'transactionsThisMonth' => $transactionsThisMonth,
            'revenueThisMonth' => $revenueThisMonth,
        ];
    }

    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $customerId = null,
        ?string $status = null
    ): LengthAwarePaginator {
        $query = MembershipTransaction::query()
            ->with(['customer', 'package', 'creator'])
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
        $package = MembershipPackage::query()->findOrFail($data['package_id']);
        $startDate = Carbon::today();

        $payload = [
            'customer_id' => $data['customer_id'],
            'package_id' => $package->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->copy()->addDays((int) $package->duration_days)->toDateString(),
            'price' => (float) $package->price,
            'status' => strtolower((string) ($data['status'] ?? 'active')),
        ];

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
