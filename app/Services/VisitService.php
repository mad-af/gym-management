<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MembershipTransaction;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class VisitService
{
    public function __construct(protected AppSettingService $appSettingService) {}

    public function getStats(): array
    {
        $today = Carbon::today();

        $visitsToday = Visit::query()
            ->whereDate('checkin_time', $today)
            ->count();

        $memberVisitsToday = Visit::query()
            ->where('visit_type', 'MEMBERSHIP')
            ->whereDate('checkin_time', $today)
            ->count();

        $dailyRevenueToday = (float) Visit::query()
            ->where('visit_type', 'DAILY')
            ->whereDate('checkin_time', $today)
            ->sum('price');

        return [
            'visitsToday' => $visitsToday,
            'memberVisitsToday' => $memberVisitsToday,
            'dailyRevenueToday' => $dailyRevenueToday,
        ];
    }

    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $customerId = null,
        ?string $visitType = null
    ): LengthAwarePaginator {
        $query = Visit::query()
            ->with(['customer', 'membershipTransaction', 'creator'])
            ->latest('checkin_time');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($visitType) {
            $query->where('visit_type', $visitType);
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

    public function create(array $data, ?string $createdBy): Visit
    {
        $payload = $data;

        if (empty($payload['customer_id']) && ! empty($payload['code'])) {
            $customer = Customer::query()
                ->where('code', $payload['code'])
                ->first();

            if (! $customer) {
                throw ValidationException::withMessages([
                    'code' => 'Kode member tidak ditemukan.',
                ]);
            }

            $payload['customer_id'] = $customer->id;
        }

        unset($payload['code']);

        if (($payload['visit_type'] ?? null) === 'DAILY') {
            $payload['price'] = $this->appSettingService->getDailyVisitPrice();
        }

        if (($payload['visit_type'] ?? null) === 'MEMBERSHIP') {
            // Find active membership if not provided
            if (empty($payload['membership_transaction_id'])) {
                $activeMembership = MembershipTransaction::query()
                    ->where('customer_id', $payload['customer_id'])
                    ->where('status', 'active')
                    ->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today())
                    ->latest('end_date')
                    ->first();

                if (! $activeMembership) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'Customer does not have an active membership for today.',
                    ]);
                }

                $payload['membership_transaction_id'] = $activeMembership->id;
            }
        }

        if ($createdBy) {
            $payload['created_by'] = $createdBy;
        }

        if (empty($payload['checkin_time'])) {
            $payload['checkin_time'] = now();
        }

        return Visit::create($payload)->refresh();
    }

    public function update(Visit $visit, array $data): Visit
    {
        $visit->update($data);

        return $visit->refresh();
    }

    public function delete(Visit $visit): bool
    {
        return (bool) $visit->delete();
    }

    public function getExportData(string $startDate, string $endDate): Collection
    {
        return Visit::query()
            ->with(['customer', 'creator'])
            ->whereDate('checkin_time', '>=', $startDate)
            ->whereDate('checkin_time', '<=', $endDate)
            ->orderBy('checkin_time')
            ->get();
    }
}
