<?php

namespace App\Services;

use App\Enums\AssetConditionEnum;
use App\Enums\AssetStatusEnum;
use App\Enums\MaintenanceStatusEnum;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\AssetMaintenanceLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetMaintenanceService
{
    public function list(array $filters = []): array
    {
        $query = AssetMaintenance::with(['asset', 'creator'])
            ->latest();

        if (! empty($filters['opd_id'])) {
            $query->opd($filters['opd_id']);
        }

        if (! empty($filters['asset_id'])) {
            $query->where('asset_id', $filters['asset_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['scheduled_from'])) {
            $query->whereDate('scheduled_date', '>=', $filters['scheduled_from']);
        }

        if (! empty($filters['scheduled_to'])) {
            $query->whereDate('scheduled_date', '<=', $filters['scheduled_to']);
        }

        $paginator = $query->paginate(
            $filters['per_page'] ?? 15,
            ['*'],
            'page',
            $filters['page'] ?? 1
        );

        $statusOptions = MaintenanceStatusEnum::toOptions();
        $conditionOptions = AssetConditionEnum::toOptions();

        return [
            'items' => $paginator,
            'status_options' => $statusOptions,
            'condition_options' => $conditionOptions,
        ];
    }

    public function detail(AssetMaintenance $maintenance): array
    {
        $maintenance->load([
            'asset.opd',
            'asset.room',
            'asset',
            'logs',
            'creator',
        ]);

        return [
            'item' => $maintenance,
            'status_options' => MaintenanceStatusEnum::toOptions(),
            'condition_options' => AssetConditionEnum::toOptions(),
        ];
    }

    public function schedule(array $data): AssetMaintenance
    {
        return DB::transaction(function () use ($data) {
            $asset = Asset::query()->findOrFail($data['asset_id']);

            $maintenance = AssetMaintenance::create([
                'asset_id' => $asset->id,
                'maintenance_type' => $data['maintenance_type'],
                'scheduled_date' => isset($data['scheduled_date']) ? Carbon::parse($data['scheduled_date']) : null,
                'cost' => $data['cost'] ?? null,
                'vendor' => $data['vendor'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => MaintenanceStatusEnum::SCHEDULED,
                'created_by' => Auth::id(),
                'condition_before' => $asset->condition,
            ]);

            $asset->status = AssetStatusEnum::UNDER_MAINTENANCE;
            $asset->save();

            AssetMaintenanceLog::create([
                'maintenance_id' => $maintenance->id,
                'notes' => 'Perawatan dijadwalkan',
                'performed_by' => Auth::id(),
            ]);

            return $maintenance->load(['asset', 'creator']);
        });
    }

    public function update(AssetMaintenance $maintenance, array $data): AssetMaintenance
    {
        return DB::transaction(function () use ($maintenance, $data) {
            $oldAssetId = $maintenance->asset_id;
            $newAssetId = $data['asset_id'];

            $maintenance->fill([
                'asset_id' => $data['asset_id'],
                'maintenance_type' => $data['maintenance_type'],
                'scheduled_date' => isset($data['scheduled_date']) ? Carbon::parse($data['scheduled_date']) : null,
                'cost' => $data['cost'] ?? null,
                'vendor' => $data['vendor'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            if ($maintenance->isDirty()) {
                $maintenance->save();

                if ($oldAssetId !== $newAssetId) {
                    // Handle old asset status
                    $oldAsset = Asset::find($oldAssetId);
                    if ($oldAsset) {
                        $hasOther = AssetMaintenance::where('asset_id', $oldAssetId)
                            ->whereIn('status', [MaintenanceStatusEnum::SCHEDULED, MaintenanceStatusEnum::IN_PROGRESS])
                            ->where('id', '!=', $maintenance->id)
                            ->exists();
                        if (! $hasOther && $oldAsset->status === AssetStatusEnum::UNDER_MAINTENANCE) {
                            $oldAsset->status = AssetStatusEnum::ACTIVE;
                            $oldAsset->save();
                        }
                    }

                    // Handle new asset status
                    $newAsset = Asset::find($newAssetId);
                    if ($newAsset) {
                        $newAsset->status = AssetStatusEnum::UNDER_MAINTENANCE;
                        $newAsset->save();
                    }
                }

                AssetMaintenanceLog::create([
                    'maintenance_id' => $maintenance->id,
                    'notes' => 'Detail perawatan diperbarui',
                    'performed_by' => Auth::id(),
                ]);
            }

            return $maintenance->load(['asset', 'creator']);
        });
    }

    public function start(AssetMaintenance $maintenance): AssetMaintenance
    {
        return DB::transaction(function () use ($maintenance) {
            $maintenance->refresh();

            if ($maintenance->status !== MaintenanceStatusEnum::SCHEDULED) {
                throw new \RuntimeException('Only scheduled maintenance can be started.');
            }

            $maintenance->status = MaintenanceStatusEnum::IN_PROGRESS;
            $maintenance->save();

            AssetMaintenanceLog::create([
                'maintenance_id' => $maintenance->id,
                'notes' => 'Maintenance started',
                'performed_by' => Auth::id(),
            ]);

            return $maintenance->load(['asset', 'logs']);
        });
    }

    public function complete(AssetMaintenance $maintenance, array $data): AssetMaintenance
    {
        return DB::transaction(function () use ($maintenance, $data) {
            $maintenance->refresh();

            if ($maintenance->status === MaintenanceStatusEnum::COMPLETED || $maintenance->status === MaintenanceStatusEnum::CANCELED) {
                throw new \RuntimeException('Maintenance is already in a terminal state.');
            }

            $asset = $maintenance->asset()->lockForUpdate()->firstOrFail();

            $finalCondition = ! empty($data['asset_condition'])
                ? AssetConditionEnum::from($data['asset_condition'])
                : AssetConditionEnum::GOOD;

            $asset->condition = $finalCondition;

            if (! empty($data['asset_status'])) {
                $asset->status = AssetStatusEnum::from($data['asset_status']);
            } elseif ($asset->status === AssetStatusEnum::UNDER_MAINTENANCE) {
                $asset->status = AssetStatusEnum::ACTIVE;
            }

            if (array_key_exists('notes', $data)) {
                $asset->notes = $data['notes'];
            }

            $asset->save();

            $maintenance->status = MaintenanceStatusEnum::COMPLETED;
            $maintenance->completed_date = isset($data['completed_date']) ? Carbon::parse($data['completed_date']) : now();
            if (array_key_exists('description', $data)) {
                $maintenance->description = $data['description'];
            }
            $maintenance->condition_after = $asset->condition;
            $maintenance->save();

            AssetMaintenanceLog::create([
                'maintenance_id' => $maintenance->id,
                'notes' => $data['log_notes'] ?? 'Perawatan selesai',
                'performed_by' => Auth::id(),
            ]);

            return $maintenance->load(['asset', 'logs']);
        });
    }

    public function cancel(AssetMaintenance $maintenance, ?string $reason = null): AssetMaintenance
    {
        return DB::transaction(function () use ($maintenance, $reason) {
            $maintenance->refresh();

            if ($maintenance->status === MaintenanceStatusEnum::COMPLETED || $maintenance->status === MaintenanceStatusEnum::CANCELED) {
                throw new \RuntimeException('Maintenance is already in a terminal state.');
            }

            $maintenance->status = MaintenanceStatusEnum::CANCELED;
            if ($reason) {
                $maintenance->description = trim(($maintenance->description ?? '').' '.$reason);
            }
            $maintenance->save();

            $asset = $maintenance->asset()->lockForUpdate()->first();
            if ($asset) {
                $hasOtherActiveMaintenances = AssetMaintenance::query()
                    ->where('asset_id', $asset->id)
                    ->whereNotIn('status', [MaintenanceStatusEnum::COMPLETED, MaintenanceStatusEnum::CANCELED])
                    ->exists();

                if (! $hasOtherActiveMaintenances && $asset->status === AssetStatusEnum::UNDER_MAINTENANCE) {
                    $asset->status = AssetStatusEnum::ACTIVE;
                    $asset->save();
                }
            }

            AssetMaintenanceLog::create([
                'maintenance_id' => $maintenance->id,
                'notes' => $reason ? 'Maintenance cancelled: '.$reason : 'Maintenance cancelled',
                'performed_by' => Auth::id(),
            ]);

            return $maintenance->load(['asset', 'logs']);
        });
    }

    public function markOverdue(AssetMaintenance $maintenance): AssetMaintenance
    {
        return DB::transaction(function () use ($maintenance) {
            $maintenance->refresh();

            if ($maintenance->status !== MaintenanceStatusEnum::SCHEDULED) {
                return $maintenance;
            }

            if ($maintenance->scheduled_date === null || $maintenance->scheduled_date->isFuture()) {
                return $maintenance;
            }

            $maintenance->status = MaintenanceStatusEnum::OVERDUE;
            $maintenance->save();

            AssetMaintenanceLog::create([
                'maintenance_id' => $maintenance->id,
                'notes' => 'Perawatan ditandai terlambat',
                'performed_by' => Auth::id(),
            ]);

            return $maintenance;
        });
    }

    public function findOverdueCandidates(Carbon $referenceDate): Collection
    {
        return AssetMaintenance::query()
            ->where('status', MaintenanceStatusEnum::SCHEDULED)
            ->whereDate('scheduled_date', '<', $referenceDate->toDateString())
            ->get();
    }

    public function findReminderCandidates(int $daysBefore): Collection
    {
        $targetDate = now()->addDays($daysBefore)->toDateString();

        return AssetMaintenance::query()
            ->where('status', MaintenanceStatusEnum::SCHEDULED)
            ->whereDate('scheduled_date', '=', $targetDate)
            ->whereNull('notification_sent_at')
            ->get();
    }

    public function getStats(?string $currentOpdId): array
    {
        $baseQuery = AssetMaintenance::query()
            ->when($currentOpdId, function ($q) use ($currentOpdId) {
                $q->whereHas('asset', function ($sub) use ($currentOpdId) {
                    $sub->where('opd_id', $currentOpdId);
                });
            });

        $totalMaintenances = (clone $baseQuery)->count();

        $scheduledMaintenances = (clone $baseQuery)
            ->where('status', MaintenanceStatusEnum::SCHEDULED)
            ->count();

        $inProgressMaintenances = (clone $baseQuery)
            ->where('status', MaintenanceStatusEnum::IN_PROGRESS)
            ->count();

        $overdueMaintenances = (clone $baseQuery)
            ->where('status', MaintenanceStatusEnum::OVERDUE)
            ->count();

        return [
            'total_maintenances' => $totalMaintenances,
            'scheduled_maintenances' => $scheduledMaintenances,
            'in_progress_maintenances' => $inProgressMaintenances,
            'overdue_maintenances' => $overdueMaintenances,
        ];
    }
}
