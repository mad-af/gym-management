<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeService
{
    public function getAll(int $perPage = 10, ?string $search = null, int $page = 1, ?string $opdId = null, ?string $status = null): LengthAwarePaginator
    {
        $query = Employee::with(['opd', 'user'])->latest();

        if ($opdId) {
            $query->opd($opdId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhereHas('opd', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSelection(int $perPage = 20, ?string $search = null, int $page = 1, bool $onlyWithoutUser = false): LengthAwarePaginator
    {
        $query = Employee::query()
            ->where('status', StatusEnum::ACTIVE)
            ->when($onlyWithoutUser, function ($q) {
                $q->whereDoesntHave('user');
            })
            ->select(['id', 'name', 'nip', 'position']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): Employee
    {
        $data['status'] = StatusEnum::ACTIVE;

        $employee = Employee::create($data);

        try {
            $generator = new AvatarGenerator;
            $generator->generateAndSave($employee->name, 'svg', $employee);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Employee avatar generation failed: '.$e->getMessage());
        }

        return $employee;
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);

        return $employee;
    }

    public function delete(Employee $employee): bool
    {
        return $this->deactivate($employee);
    }

    public function deactivate(Employee $employee): bool
    {
        return $employee->update(['status' => StatusEnum::INACTIVE]);
    }

    public function activate(Employee $employee): bool
    {
        return $employee->update(['status' => StatusEnum::ACTIVE]);
    }
}
