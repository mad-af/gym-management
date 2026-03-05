<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1
    ): LengthAwarePaginator {
        $query = Customer::query()->latest('created_at');

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
}
