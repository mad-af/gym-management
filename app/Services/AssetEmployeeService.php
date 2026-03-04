<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Support\Facades\DB;

class AssetEmployeeService
{
    public function show(Asset $asset): ?string
    {
        return $asset->employee_id;
    }

    public function update(Asset $asset, ?string $employeeId): Asset
    {
        return DB::transaction(function () use ($asset, $employeeId) {
            $asset->employee_id = $employeeId;
            $asset->save();

            return $asset->load(['employee']);
        });
    }
}
