<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OpdService
{
    public function getAll(?User $user, int $perPage = 10, ?string $search = null, int $page = 1, ?string $status = null, ?string $currentOpdId = null): LengthAwarePaginator
    {
        $query = Opd::query()->with('head')->latest();

        // Filter by current OPD if provided (and user doesn't have all OPDs access)
        if ($currentOpdId && $user && ! $user->has_all_opds) {
            $query->where('id', $currentOpdId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(?User $user, int $perPage = 20, ?string $search = null, int $page = 1, ?string $currentOpdId = null): LengthAwarePaginator
    {
        $query = Opd::query()
            ->where('status', StatusEnum::ACTIVE)
            ->select(['id', 'name', 'code']);

        // Filter by current OPD if provided (and user doesn't have all OPDs access)
        if ($currentOpdId && $user && ! $user->has_all_opds) {
            $query->where('id', $currentOpdId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): Opd
    {
        $data['status'] = StatusEnum::ACTIVE;

        $opd = Opd::create($data);

        $this->attachToAllAccessUsers($opd);

        return $opd;
    }

    public function update(Opd $opd, array $data): Opd
    {
        $opd->update($data);

        return $opd;
    }

    public function delete(Opd $opd): bool
    {
        return $this->deactivate($opd);
    }

    public function deactivate(Opd $opd): bool
    {
        return $opd->update(['status' => StatusEnum::INACTIVE]);
    }

    public function activate(Opd $opd): bool
    {
        return $opd->update(['status' => StatusEnum::ACTIVE]);
    }

    protected function attachToAllAccessUsers(Opd $opd): void
    {
        $users = User::query()
            ->where('has_all_opds', true)
            ->pluck('id');

        if ($users->isEmpty()) {
            return;
        }

        $opd->users()->syncWithoutDetaching($users->all());
    }
}
