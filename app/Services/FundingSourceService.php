<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Models\FundingSource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FundingSourceService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $status = null
    ): LengthAwarePaginator {
        $query = FundingSource::query()->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(
        int $perPage = 20,
        ?string $search = null,
        int $page = 1
    ): LengthAwarePaginator {
        $query = FundingSource::query()
            ->where('status', StatusEnum::ACTIVE)
            ->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): FundingSource
    {
        $data['status'] = StatusEnum::ACTIVE;

        return FundingSource::create($data);
    }

    public function update(FundingSource $fundingSource, array $data): FundingSource
    {
        $fundingSource->update($data);

        return $fundingSource;
    }

    public function delete(FundingSource $fundingSource): bool
    {
        return $this->deactivate($fundingSource);
    }

    public function deactivate(FundingSource $fundingSource): bool
    {
        return $fundingSource->update(['status' => StatusEnum::INACTIVE]);
    }

    public function activate(FundingSource $fundingSource): bool
    {
        return $fundingSource->update(['status' => StatusEnum::ACTIVE]);
    }
}
