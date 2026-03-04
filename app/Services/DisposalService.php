<?php

namespace App\Services;

use App\Enums\AssetConditionEnum;
use App\Enums\AssetStatusEnum;
use App\Enums\DisposalTypeEnum;
use App\Models\Asset;
use App\Models\DisposalDocument;
use App\Models\DisposalItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DisposalService
{
    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $opdId = null,
        ?string $disposalType = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): LengthAwarePaginator {
        $query = DisposalDocument::query()
            ->with([
                'opd',
                'creator',
            ])
            ->withCount('items')
            ->latest('disposal_date')
            ->latest('created_at');

        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('disposal_number', 'like', "%{$search}%");
            });
        }

        if ($opdId) {
            $query->opd($opdId);
        }

        if ($disposalType) {
            $query->where('disposal_type', $disposalType);
        }

        if ($dateFrom) {
            $query->whereDate('disposal_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('disposal_date', '<=', $dateTo);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById(string $id): DisposalDocument
    {
        return DisposalDocument::with([
            'opd',
            'creator',
            'items.asset.category',
            'items.asset.opd',
            'items.asset.room',
        ])->findOrFail($id);
    }

    public function createDisposalDocument(array $data, string $createdByUserId, ?string $currentOpdId): DisposalDocument
    {
        return DB::transaction(function () use ($data, $createdByUserId, $currentOpdId) {
            $opdId = $data['opd_id'];

            if ($currentOpdId && $opdId !== $currentOpdId) {
                abort(403, 'You can only create disposal documents for your current OPD.');
            }

            if (! in_array($data['disposal_type'], DisposalTypeEnum::values(), true)) {
                abort(422, 'Invalid disposal type.');
            }

            $itemsInput = $data['items'] ?? [];

            if (empty($itemsInput)) {
                abort(422, 'At least one asset must be selected for disposal.');
            }

            $assetIds = collect($itemsInput)->pluck('asset_id')->all();

            $assets = Asset::query()
                ->whereIn('id', $assetIds)
                ->with(['opd'])
                ->get()
                ->keyBy('id');

            if ($assets->count() !== count($assetIds)) {
                abort(422, 'One or more assets could not be found.');
            }

            foreach ($assets as $asset) {
                if ($asset->opd_id !== $opdId) {
                    abort(422, 'All assets must belong to the selected OPD.');
                }

                if ($asset->status === AssetStatusEnum::DISPOSED) {
                    abort(422, 'Asset is already disposed.');
                }

                if (! $asset->condition instanceof AssetConditionEnum || ! $asset->condition->isDisposable()) {
                    abort(422, 'Asset condition is not eligible for disposal.');
                }
            }

            $disposal = DisposalDocument::create([
                'disposal_number' => $this->generateDisposalNumber(),
                'opd_id' => $opdId,
                'disposal_type' => $data['disposal_type'],
                'disposal_date' => $data['disposal_date'],
                'created_by' => $createdByUserId,
                'notes' => $data['notes'] ?? null,
                'document_path' => $data['document_path'] ?? null,
            ]);

            foreach ($itemsInput as $itemData) {
                $asset = $assets->get($itemData['asset_id']);

                DisposalItem::create([
                    'disposal_document_id' => $disposal->id,
                    'asset_id' => $asset->id,
                    'reason' => $itemData['reason'],
                    'condition_at_disposal' => $itemData['condition_at_disposal'],
                ]);

                $asset->status = AssetStatusEnum::DISPOSED;
                $asset->room_id = null;
                $asset->save();
            }

            return $this->findById($disposal->id);
        });
    }

    public function getStats(?string $currentOpdId): array
    {
        $totalAssets = Asset::query()
            ->when($currentOpdId, function (Builder $q) use ($currentOpdId) {
                $q->where('opd_id', $currentOpdId);
            })
            ->count();

        $totalDisposalDocuments = DisposalDocument::query()
            ->when($currentOpdId, function (Builder $q) use ($currentOpdId) {
                $q->where('opd_id', $currentOpdId);
            })
            ->count();

        $totalDisposedAssets = Asset::query()
            ->when($currentOpdId, function (Builder $q) use ($currentOpdId) {
                $q->where('opd_id', $currentOpdId);
            })
            ->where('status', AssetStatusEnum::DISPOSED)
            ->count();

        $totalMajorDamageNotDisposed = Asset::query()
            ->when($currentOpdId, function (Builder $q) use ($currentOpdId) {
                $q->where('opd_id', $currentOpdId);
            })
            ->where('condition', AssetConditionEnum::MAJOR_DAMAGE)
            ->where('status', '!=', AssetStatusEnum::DISPOSED)
            ->count();

        $totalMajorDamageAssets = Asset::query()
            ->when($currentOpdId, function (Builder $q) use ($currentOpdId) {
                $q->where('opd_id', $currentOpdId);
            })
            ->where('condition', AssetConditionEnum::MAJOR_DAMAGE)
            ->count();

        $percentageMajorDamageDisposed = 0;

        if ($totalMajorDamageAssets > 0) {
            $disposedMajorDamage = $totalMajorDamageAssets - $totalMajorDamageNotDisposed;
            $percentageMajorDamageDisposed = round(($disposedMajorDamage / $totalMajorDamageAssets) * 100, 2);
        }

        return [
            'total_assets' => $totalAssets,
            'total_disposal_documents' => $totalDisposalDocuments,
            'total_disposed_assets' => $totalDisposedAssets,
            'total_major_damage_not_disposed' => $totalMajorDamageNotDisposed,
            'percentage_major_damage_disposed' => $percentageMajorDamageDisposed,
        ];
    }

    protected function generateDisposalNumber(): string
    {
        $prefix = 'DSP-'.now()->format('Ymd').'-';

        do {
            $number = $prefix.Str::upper(Str::random(5));
        } while (DisposalDocument::where('disposal_number', $number)->exists());

        return $number;
    }
}
