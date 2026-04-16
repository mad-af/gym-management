<?php

namespace App\Services;

use App\Models\MembershipPackage;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class MembershipTransactionService
{
    public function getStats(): array
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $activeMembers = MembershipTransaction::query()
            ->notCancelled()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->distinct('customer_id')
            ->count('customer_id');

        $transactionsThisMonth = MembershipTransaction::query()
            ->notCancelled()
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $endOfMonth)
            ->count();

        $revenueThisMonth = (float) MembershipTransaction::query()
            ->notCancelled()
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $endOfMonth)
            ->sum('price');

        $baseExpiringQuery = MembershipTransaction::query()
            ->notCancelled()
            ->where('status', 'active')
            ->whereDate('end_date', '>=', $today);

        return [
            'activeMembers' => $activeMembers,
            'transactionsThisMonth' => $transactionsThisMonth,
            'revenueThisMonth' => $revenueThisMonth,
            'expiringCount' => [
                '3_days' => (int) (clone $baseExpiringQuery)->whereDate('end_date', '<=', $today->copy()->addDays(3))->count(),
                '7_days' => (int) (clone $baseExpiringQuery)->whereDate('end_date', '<=', $today->copy()->addDays(7))->count(),
                '14_days' => (int) (clone $baseExpiringQuery)->whereDate('end_date', '<=', $today->copy()->addDays(14))->count(),
                '30_days' => (int) (clone $baseExpiringQuery)->whereDate('end_date', '<=', $today->copy()->addDays(30))->count(),
            ],
        ];
    }

    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $customerId = null,
        ?string $status = null,
        ?string $createdBy = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $expiringWithinDays = null,
        ?bool $last24Hours = false
    ): LengthAwarePaginator {
        $query = MembershipTransaction::query()
            ->with(['customer', 'package', 'creator', 'cancelledBy'])
            ->notCancelled()
            ->latest('created_at');

        if ($last24Hours) {
            $query->where('created_at', '>=', Carbon::now()->subHours(24));
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($status) {
            $query->where('status', $status);
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
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($expiringWithinDays !== null) {
            $today = Carbon::today();
            $query->where('status', 'active')
                ->whereDate('end_date', '>=', $today)
                ->whereDate('end_date', '<=', $today->copy()->addDays($expiringWithinDays));
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getExpiring(int $days = 7): Collection
    {
        $today = Carbon::today();

        return MembershipTransaction::query()
            ->notCancelled()
            ->with(['customer', 'package', 'creator'])
            ->where('status', 'active')
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $today->copy()->addDays($days))
            ->orderBy('end_date')
            ->get();
    }

    public function create(array $data, ?string $createdBy): MembershipTransaction
    {
        $package = MembershipPackage::query()->findOrFail($data['package_id']);
        $startDate = Carbon::today();

        $payload = [
            'customer_id' => $data['customer_id'],
            'package_id' => $package->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->copy()->addDays((int) $package->duration_days - 1)->toDateString(),
            'price' => (float) $package->price,
            'payment_type' => $data['payment_type'] ?? 'CASH',
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

    public function cancel(MembershipTransaction $transaction, string $reason, User $user): MembershipTransaction
    {
        if ($transaction->cancelled_at !== null) {
            throw ValidationException::withMessages([
                'membership_transaction_id' => 'Membership transaction is already cancelled.',
            ]);
        }

        $hasVisits = Visit::query()
            ->where('membership_transaction_id', $transaction->id)
            ->where('visit_type', 'MEMBERSHIP')
            ->notCancelled()
            ->whereDate('checkin_time', '>=', $transaction->start_date)
            ->whereDate('checkin_time', '<=', $transaction->end_date)
            ->exists();

        if ($hasVisits) {
            throw ValidationException::withMessages([
                'membership_transaction_id' => 'Membership cannot be cancelled because customer has already visited.',
            ]);
        }

        $transaction->update([
            'cancellation_reason' => $reason,
            'cancelled_by' => $user->id,
            'cancelled_at' => Carbon::now(),
        ]);

        return $transaction->fresh(['customer', 'package', 'creator', 'cancelledBy']);
    }

    public function getExportData(string $startDate, string $endDate): Collection
    {
        return MembershipTransaction::query()
            ->with(['customer', 'package', 'creator', 'cancelledBy'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at')
            ->get();
    }
}
