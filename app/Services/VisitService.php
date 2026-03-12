<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MembershipTransaction;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class VisitService
{
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

        if (empty($payload['customer_id']) && ! empty($payload['qr_code'])) {
            $customer = Customer::query()
                ->where('qr_code', $payload['qr_code'])
                ->first();

            if (! $customer) {
                throw ValidationException::withMessages([
                    'qr_code' => 'QR code tidak ditemukan.',
                ]);
            }

            $payload['customer_id'] = $customer->id;
        }

        unset($payload['qr_code']);

        if (($payload['visit_type'] ?? null) === 'DAILY') {
            $hasPrice = array_key_exists('price', $payload) && $payload['price'] !== null;

            if (! $hasPrice) {
                throw ValidationException::withMessages([
                    'price' => 'Harga wajib diisi untuk kunjungan harian.',
                ]);
            }
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
}
