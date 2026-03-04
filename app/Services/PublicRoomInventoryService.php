<?php

namespace App\Services;

use App\Enums\AssetConditionEnum;
use App\Enums\AssetStatusEnum;
use App\Models\Asset;
use App\Models\Room;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PublicRoomInventoryService
{
    /**
     * Get room inventory detail for public view.
     */
    public function getRoomInventoryDetail(string $roomId): array
    {
        $room = Room::with('opd')->findOrFail($roomId);

        $baseQuery = Asset::query()
            ->where('room_id', $room->id)
            ->where('status', '!=', AssetStatusEnum::DISPOSED); // Exclude disposed assets for public stats too? Probably yes.

        $totalAssets = (clone $baseQuery)->count();

        $byStatus = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'room' => $room,
            'stats' => [
                'total_assets' => $totalAssets,
                'by_status' => $byStatus,
            ],
        ];
    }

    /**
     * Get room assets for public view with pagination and filtering.
     */
    public function getRoomAssets(string $roomId, int $perPage = 10, ?string $search = null, int $page = 1, ?string $status = null, ?string $condition = null): array
    {
        $query = Asset::query()
            ->where('room_id', $roomId)
            ->where('status', '!=', AssetStatusEnum::DISPOSED) // Exclude disposed assets
            ->with(['category', 'opd', 'room']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($condition) {
            $query->where('condition', $condition);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('asset_code')->paginate($perPage, ['*'], 'page', $page);

        $statusOptions = AssetStatusEnum::toOptions();
        $conditionOptions = AssetConditionEnum::toOptions();

        return [
            'items' => $paginator,
            'status_options' => $statusOptions,
            'condition_options' => $conditionOptions,
        ];
    }

    /**
     * Generate PDF for room inventory (public access).
     */
    public function generateRoomInventoryPdf(string $roomId): Response
    {
        $room = Room::with('opd')->findOrFail($roomId);

        // Ubah ke true untuk debug tampilan HTML di browser
        // Ubah ke false untuk generate PDF download
        $debug = false;

        $assets = Asset::query()
            ->where('room_id', $room->id)
            ->where('status', '!=', AssetStatusEnum::DISPOSED)
            ->with(['category', 'additionalInfo'])
            ->orderBy('asset_code')
            ->get();

        $qrValue = $room->qr_id ? route('qr.redirect', ['type' => 'r', 'qr_id' => $room->qr_id]) : null;
        $qrImage = $qrValue ? \App\Helpers\QrCodeHelper::generateBase64Svg($qrValue) : null;

        $html = view('pdf.room_inventory', [
            'room' => $room,
            'assets' => $assets,
            'qr_value' => $qrValue,
            'qr_image' => $qrImage,
        ])->render();

        if ($debug) {
            return response($html)->header('Content-Type', 'text/html');
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A5-L',
            'margin_left' => 6,
            'margin_right' => 6,
            'margin_top' => 6,
            'margin_bottom' => 6,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('room-inventory-'.$room->code.'.pdf', \Mpdf\Output\Destination::INLINE))
            ->header('Content-Type', 'application/pdf');
    }
}
