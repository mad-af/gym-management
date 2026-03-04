<?php

namespace App\Services;

use App\Enums\AssetConditionEnum;
use App\Enums\AssetStatusEnum;
use App\Models\Asset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetService
{
    public function generateLabelPdf(Asset $asset, string $paperSize = 'label'): Response
    {
        // Ubah ke true untuk debug tampilan HTML di browser
        // Ubah ke false untuk generate PDF download
        $debug = false;

        if ($asset->status === AssetStatusEnum::DISPOSED) {
            abort(403, 'Tidak dapat mencetak label untuk aset yang sudah dibuang/dihapus.');
        }

        $asset->load(['category', 'opd', 'room', 'employee', 'additionalInfo']);

        $qrValue = $asset->qr_id ? route('qr.redirect', ['type' => 'a', 'qr_id' => $asset->qr_id]) : $asset->asset_code;
        $qrImage = \App\Helpers\QrCodeHelper::generateBase64Svg($qrValue);

        $items = [
            [
                'asset' => $asset,
                'qr_image' => $qrImage,
            ],
        ];

        $viewName = $paperSize === 'a4' ? 'pdf.asset_label_a4' : 'pdf.asset_label';

        $html = view($viewName, [
            'items' => $items,
            'paperSize' => $paperSize,
        ])->render();

        if ($debug) {
            return response($html)->header('Content-Type', 'text/html');
        }

        $format = $paperSize === 'label' ? [50, 30] : 'A4';
        // Jika label, margin 0 agar full 3x5cm. Jika A4, margin 10mm (atau 1cm).
        $margin = $paperSize === 'label' ? 0 : 10;

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => $format,
            'margin_left' => $margin,
            'margin_right' => $margin,
            'margin_top' => $margin,
            'margin_bottom' => $margin,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('label-'.$asset->asset_code.'.pdf', \Mpdf\Output\Destination::INLINE))
            ->header('Content-Type', 'application/pdf');
    }

    public function generateBulkLabelPdf(array $assetIds, string $paperSize = 'label'): Response
    {
        // Ubah ke true untuk debug tampilan HTML di browser
        // Ubah ke false untuk generate PDF download
        $debug = false;

        $assets = Asset::whereIn('id', $assetIds)
            ->where('status', '!=', AssetStatusEnum::DISPOSED)
            ->with(['category', 'opd', 'room', 'employee', 'additionalInfo'])
            ->get();

        if ($assets->isEmpty()) {
            abort(404, 'Tidak ada aset yang ditemukan atau semua aset sudah dibuang/dihapus.');
        }

        $items = $assets->map(function ($asset) {
            $qrValue = $asset->qr_id ? route('qr.redirect', ['type' => 'a', 'qr_id' => $asset->qr_id]) : $asset->asset_code;
            $qrImage = \App\Helpers\QrCodeHelper::generateBase64Svg($qrValue);

            return [
                'asset' => $asset,
                'qr_image' => $qrImage,
            ];
        });

        $viewName = $paperSize === 'a4' ? 'pdf.asset_label_a4' : 'pdf.asset_label';

        $html = view($viewName, [
            'items' => $items,
            'paperSize' => $paperSize,
        ])->render();

        if ($debug) {
            return response($html)->header('Content-Type', 'text/html');
        }

        $format = $paperSize === 'label' ? [50, 30] : 'A4';
        // Jika label, margin 0 agar full 3x5cm. Jika A4, margin 10mm (atau 1cm).
        $margin = $paperSize === 'label' ? 0 : 10;

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => $format,
            'margin_left' => $margin,
            'margin_right' => $margin,
            'margin_top' => $margin,
            'margin_bottom' => $margin,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('bulk-labels.pdf', \Mpdf\Output\Destination::INLINE))
            ->header('Content-Type', 'application/pdf');
    }

    public function getAll(
        int $perPage = 10,
        ?string $search = null,
        int $page = 1,
        ?string $opdId = null,
        ?string $status = null,
        ?string $categoryId = null,
        ?string $roomId = null
    ): array {
        $query = Asset::query()
            ->with(['category', 'opd', 'room', 'fundingSource'])
            ->latest();

        if ($opdId) {
            $query->opd($opdId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $statusOptions = AssetStatusEnum::toOptions();

        $conditionOptions = AssetConditionEnum::toOptions();

        return [
            'items' => $paginator,
            'status_options' => $statusOptions,
            'condition_options' => $conditionOptions,
        ];
    }

    public function getSelection(
        int $perPage = 20,
        ?string $search = null,
        int $page = 1,
        ?string $opdId = null,
        bool $onlyDisposable = false,
        bool $onlyMaintainable = false
    ): LengthAwarePaginator {
        $query = Asset::query()
            ->with(['room:id,name'])
            ->select(['id', 'asset_code', 'name', 'opd_id', 'room_id', 'status', 'condition']);

        if ($onlyMaintainable) {
            $query->whereIn('condition', [
                AssetConditionEnum::MINOR_DAMAGE,
                AssetConditionEnum::MAJOR_DAMAGE,
            ])->whereNotIn('status', [
                AssetStatusEnum::DISPOSED,
                AssetStatusEnum::UNDER_MAINTENANCE,
            ]);
        } elseif ($onlyDisposable) {
            $query->whereIn('condition', [
                AssetConditionEnum::MAJOR_DAMAGE,
                AssetConditionEnum::LOST,
            ])->where('status', '!=', AssetStatusEnum::DISPOSED);
        } else {
            $query->where('status', AssetStatusEnum::ACTIVE);
        }

        if ($opdId) {
            $query->opd($opdId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data): Asset
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = AssetStatusEnum::ACTIVE;
            $data['qr_id'] = $data['qr_id'] ?? $this->generateQrId();
            $data['created_by'] = $data['created_by'] ?? Auth::id();

            /** @var Asset $asset */
            $asset = Asset::create($data);

            return $asset->load(['category', 'opd', 'room', 'fundingSource']);
        });
    }

    public function update(Asset $asset, array $data): Asset
    {
        return DB::transaction(function () use ($asset, $data) {
            $asset->update($data);

            $asset->refresh();

            return $asset->load(['category', 'opd', 'room', 'fundingSource']);
        });
    }

    public function updateStatus(Asset $asset, string $status): Asset
    {
        if ($asset->condition === AssetConditionEnum::LOST) {
            throw new \Exception('Tidak dapat mengubah status aset yang hilang.');
        }

        return DB::transaction(function () use ($asset, $status) {
            $asset->update(['status' => $status]);

            return $asset->refresh()->load(['category', 'opd', 'room', 'fundingSource']);
        });
    }

    public function updateCondition(Asset $asset, string $condition): Asset
    {
        return DB::transaction(function () use ($asset, $condition) {
            $data = ['condition' => $condition];

            // Jika kondisi adalah LOST (Hilang), set status menjadi INACTIVE
            if ($condition === AssetConditionEnum::LOST->value) {
                $data['status'] = AssetStatusEnum::INACTIVE->value;
            }

            $asset->update($data);

            return $asset->refresh()->load(['category', 'opd', 'room', 'fundingSource']);
        });
    }

    public function delete(Asset $asset): bool
    {
        return DB::transaction(function () use ($asset) {
            $deleted = (bool) $asset->delete();

            return $deleted;
        });
    }

    public function deactivate(Asset $asset): bool
    {
        return $asset->update(['status' => AssetStatusEnum::INACTIVE]);
    }

    public function activate(Asset $asset): bool
    {
        return $asset->update(['status' => AssetStatusEnum::ACTIVE]);
    }

    public function getStats(?string $currentOpdId): array
    {
        $baseQuery = Asset::query()
            ->where('status', '!=', AssetStatusEnum::DISPOSED)
            ->when($currentOpdId, function (Builder $q) use ($currentOpdId) {
                $q->where('opd_id', $currentOpdId);
            });

        $totalAssets = (clone $baseQuery)->count();

        $assetsWithPriceQuery = (clone $baseQuery)
            ->whereNotNull('purchase_price');

        $assetsWithPrice = (clone $assetsWithPriceQuery)->count();

        $activeAssets = (clone $baseQuery)
            ->where('status', AssetStatusEnum::ACTIVE)
            ->count();

        $assetsWithoutPrice = max($totalAssets - $assetsWithPrice, 0);

        $totalValuedAssets = (clone $assetsWithPriceQuery)->sum('purchase_price');

        return [
            'total_assets' => $totalAssets,
            'total_valued_assets' => $totalValuedAssets,
            'assets_with_price' => $assetsWithPrice,
            'assets_without_price' => $assetsWithoutPrice,
            'active_assets' => $activeAssets,
        ];
    }

    protected function generateQrId(): string
    {
        $length = 8;
        do {
            $id = Str::random($length);
        } while (Asset::where('qr_id', $id)->exists());

        return $id;
    }
}
