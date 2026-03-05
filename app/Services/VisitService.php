<?php

namespace App\Services;

use App\Models\Visit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
            ->with(['customer', 'membershipTransaction'])
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

        if ($createdBy) {
            $payload['created_by'] = $createdBy;
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
