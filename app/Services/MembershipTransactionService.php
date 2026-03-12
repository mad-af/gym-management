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
        $payload = $data;

        // Fetch Package Details
        $package = MembershipPackage::findOrFail($data['package_id']);

        // Set Start Date (Default to today if not provided)
        $startDate = isset($data['start_date']) ? \Carbon\Carbon::parse($data['start_date']) : now();
        $payload['start_date'] = $startDate->toDateString();

        // Calculate End Date
        if (! isset($data['end_date'])) {
            $payload['end_date'] = $startDate->copy()->addDays($package->duration_days)->toDateString();
        }

        // Set Price from Package if not provided
        if (! isset($data['price'])) {
            $payload['price'] = $package->price;
        }

        // Default Status
        if (! isset($data['status'])) {
            $payload['status'] = 'active'; // Default status
        }

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
