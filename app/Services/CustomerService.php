<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MembershipTransaction;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1
    ): LengthAwarePaginator {
        $now = Carbon::now()->startOfDay();
        $query = Customer::query()
            ->with(['membershipTransactions' => function ($q) use ($now) {
                $q->whereDate('start_date', '<=', $now)
                    ->whereDate('end_date', '>=', $now)
                    ->with('package');
            }])
            ->latest('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(
        int $perPage = 20,
        ?string $search = null,
        int $page = 1
    ): LengthAwarePaginator {
        $query = Customer::query()->latest('created_at');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->select(['id', 'name'])->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->refresh();
    }

    public function delete(Customer $customer): bool
    {
        return (bool) $customer->delete();
    }

    public function getStats(): array
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $totalCustomers = Customer::query()->count();

        $activeMembers = Customer::query()
            ->whereHas('membershipTransactions', function ($q) use ($today) {
                $q->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->where('status', 'active');
            })
            ->count();

        $activeThisMonth = MembershipTransaction::query()
            ->where('status', 'active')
            ->whereDate('start_date', '>=', $startOfMonth)
            ->whereDate('start_date', '<=', $endOfMonth)
            ->distinct('customer_id')
            ->count('customer_id');

        $visitsToday = Visit::query()
            ->whereDate('checkin_time', $today)
            ->count();

        return [
            'total' => $totalCustomers,
            'activeThisMonth' => $activeThisMonth,
            'activeMembers' => $activeMembers,
            'visitsToday' => $visitsToday,
        ];
    }
}
